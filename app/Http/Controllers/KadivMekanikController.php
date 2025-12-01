<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Session;
use App\Models\LaporanHarianMekanik;
use App\Models\LaporanPemakaianBarang;
use App\Models\SuratPengajuan;
use App\Models\Divisi;
use App\Models\Unit;
use App\Models\Akun;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class KadivMekanikController extends Controller
{
    /**
     * Calculate week date range for real calendar weeks (Monday to Sunday) using Indonesia timezone
     */
    private function calculateWeekRange($tahun, $bulan, $weekNumber)
    {
        // Get the first day of the month using Indonesia timezone
        $firstDayOfMonth = new \DateTime($tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-01', new \DateTimeZone('Asia/Makassar'));
        
        // Get the last day of the month
        $lastDayOfMonth = clone $firstDayOfMonth;
        $lastDayOfMonth->modify('last day of this month');
        $lastDay = (int)$lastDayOfMonth->format('d');
        
        // Calculate week range based on date ranges in the month
        // Minggu 1: tanggal 1-7
        // Minggu 2: tanggal 8-14
        // Minggu 3: tanggal 15-21
        // Minggu 4: tanggal 22-28
        // Minggu 5: tanggal 29 sampai akhir bulan (jika ada)
        
        $startDay = (($weekNumber - 1) * 7) + 1;
        $endDay = min($startDay + 6, $lastDay);
        
        // If week number is beyond the month, return null
        if ($startDay > $lastDay) {
            return null;
        }
        
        // Create start and end dates
        $weekStartDate = new \DateTime($tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-' . str_pad($startDay, 2, '0', STR_PAD_LEFT), new \DateTimeZone('Asia/Makassar'));
        $weekEndDate = new \DateTime($tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-' . str_pad($endDay, 2, '0', STR_PAD_LEFT), new \DateTimeZone('Asia/Makassar'));
        
        return [
            'start' => $weekStartDate->format('Y-m-d'),
            'end' => $weekEndDate->format('Y-m-d')
        ];
    }

    /**
     * Display the dashboard page.
     */
    public function dashboard()
    {
        // Get statistics for dashboard
        $totalWorkOrder = SuratPengajuan::count();
        $totalLaporanHarian = LaporanHarianMekanik::count();
        $totalLaporanBarang = LaporanPemakaianBarang::count();
        $workOrderPending = SuratPengajuan::where('status', 'Menunggu')->count();

        // ========== DATA CHART REAL DARI DATABASE ==========
        
        // 1. Status Work Order yang Dikelola
        $managedWorkOrderStatus = SuratPengajuan::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status')
            ->toArray();

        // 2. Trend Laporan (3 bulan terakhir - lebih sederhana)
        $monthlyReportTrend = LaporanHarianMekanik::select(
                DB::raw('MONTH(tanggal) as month'),
                DB::raw('YEAR(tanggal) as year'),
                DB::raw('count(*) as total')
            )
            ->where('tanggal', '>=', DB::raw('DATE_SUB(NOW(), INTERVAL 3 MONTH)'))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // 3. Pemakaian Barang per Unit
        $materialUsagePerUnit = LaporanPemakaianBarang::select('kode_unit', DB::raw('SUM(total_harga) as total'))
            ->whereNotNull('kode_unit')
            ->groupBy('kode_unit')
            ->get()
            ->pluck('total', 'kode_unit')
            ->toArray();

        // 4. Aktivitas Terbaru
        $recentActivities = SuratPengajuan::join('karyawan', 'surat_pengajuan.id_akun', '=', 'karyawan.id_karyawan')
            ->select('surat_pengajuan.*', 'karyawan.nama_lengkap')
            ->orderBy('surat_pengajuan.created_at', 'desc')
            ->limit(5)
            ->get();

        return view('kadivmekanik.dashboard', compact(
            'totalWorkOrder', 
            'totalLaporanHarian', 
            'totalLaporanBarang', 
            'workOrderPending',
            'managedWorkOrderStatus',
            'monthlyReportTrend',
            'materialUsagePerUnit',
            'recentActivities'
        ));
    }

    /**
     * Display work order page (WO yang dibuat oleh divisi pengaju).
     */
    public function workOrder()
    {
        $userDivisi = Session::get('user_divisi');
        
        // Ambil nama divisi user yang login
        $userDivisiNama = DB::table('divisi')->where('id_divisi', $userDivisi)->value('nama_divisi') ?? 'Mekanik';
        
        // WO yang dibuat oleh divisi pengaju (akun yang login)
        $workOrders = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder'])
            ->where('divisi_pengaju', $userDivisiNama)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return (object)[
                    'id' => $item->id_surat_pengajuan,
                    'id_surat_pengajuan' => $item->id_surat_pengajuan,
                    'no_surat_pengajuan' => $item->no_surat_pengajuan,
                    'no_work_order' => $item->no_surat_pengajuan,
                    'divisi_pengaju' => $item->divisi_pengaju,
                    'ditujukan' => $item->ditujukan,
                    'tanggal' => $item->tanggal,
                    'unit_code' => $item->unit ?: '',
                    'uraian' => $item->uraian,
                    'dokumentasi' => $item->dokumentasi,
                    'status' => $item->verifikator->nama_status ?? ($item->status ?? 'Menunggu'),
                    'id_verifikator' => $item->id_verifikator,
                    'verifikator' => $item->verifikator,
                    'divisi_nama' => $item->divisi->nama_divisi ?? '',
                    'unit_nama' => $item->getRelation('unit') ? $item->getRelation('unit')->nama_unit : '',
                    'akun_email' => $item->akun->email ?? '',
                    'jenisWorkOrder' => $item->jenisWorkOrder
                ];
            });

        // Ambil data untuk dropdown
        $divisi = Divisi::where('nama_divisi', '!=', 'Administrator')->get();
        $unit = Unit::all();
        $karyawan = DB::table('karyawan')->get();
        $unitOptions = Unit::all();
        $jenisWorkOrder = \App\Models\JenisWorkOrder::all();
        $daftarBarang = \App\Models\DaftarBarang::all();
        
        $nextWorkOrderNumber = $this->generateWorkOrderNumber();

        return view('kadivmekanik.work_order', compact('workOrders', 'divisi', 'unit', 'nextWorkOrderNumber', 'karyawan', 'unitOptions', 'jenisWorkOrder', 'daftarBarang'));
    }

    /**
     * Display daftar pengajuan work order page (WO yang diterima dari divisi lain).
     */
    public function daftarPengajuanWorkOrder()
    {
        $userDivisi = Session::get('user_divisi');
        
        // Ambil nama divisi user yang login
        $userDivisiNama = DB::table('divisi')->where('id_divisi', $userDivisi)->value('nama_divisi') ?? 'Mekanik';
        
        // WO yang diterima oleh divisi user yang login (dari divisi lain)
        // Hanya menampilkan yang status Menunggu (id_verifikator = 1)
        $submissionWorkOrders = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder'])
            ->where('ditujukan', $userDivisiNama)
            ->where('divisi_pengaju', '!=', $userDivisiNama)
            ->where('id_verifikator', 1) // Hanya status Menunggu (1)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return (object)[
                    'id' => $item->id_surat_pengajuan,
                    'id_surat_pengajuan' => $item->id_surat_pengajuan,
                    'no_surat_pengajuan' => $item->no_surat_pengajuan,
                    'no_work_order' => $item->no_surat_pengajuan,
                    'divisi_pengaju' => $item->divisi_pengaju,
                    'ditujukan' => $item->ditujukan,
                    'tanggal' => $item->tanggal,
                    'unit_code' => $item->unit ?: '',
                    'uraian' => $item->uraian,
                    'dokumentasi' => $item->dokumentasi,
                    'status' => $item->verifikator->nama_status ?? ($item->status ?? 'Menunggu'),
                    'id_verifikator' => $item->id_verifikator,
                    'divisi_nama' => $item->divisi->nama_divisi ?? '',
                    'unit_nama' => $item->getRelation('unit') ? $item->getRelation('unit')->nama_unit : '',
                    'akun_email' => $item->akun->email ?? '',
                    'jenisWorkOrder' => $item->jenisWorkOrder
                ];
            });

        return view('kadivmekanik.daftar_pengajuan_work_order', compact('submissionWorkOrders'));
    }

    /**
     * Display riwayat work order page (WO yang sudah selesai).
     */
    public function riwayatWorkOrder()
    {
        $userDivisi = Session::get('user_divisi');
        
        // Ambil nama divisi user yang login
        $userDivisiNama = DB::table('divisi')->where('id_divisi', $userDivisi)->value('nama_divisi') ?? 'Mekanik';
        
        // WO yang sudah selesai (status Disetujui - id_verifikator = 2) atau Ditolak (id_verifikator = 3)
        // Gabungkan WO yang dibuat dan diterima oleh divisi user yang login
        $workOrdersDibuat = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder'])
            ->where('divisi_pengaju', $userDivisiNama)
            ->whereIn('id_verifikator', [2, 3]) // Status Disetujui (2) dan Ditolak (3)
            ->get();
        
        $workOrdersDiterima = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder'])
            ->where('ditujukan', $userDivisiNama)
            ->where('divisi_pengaju', '!=', $userDivisiNama)
            ->whereIn('id_verifikator', [2, 3]) // Status Disetujui (2) dan Ditolak (3)
            ->get();
        
        $workOrders = $workOrdersDibuat->merge($workOrdersDiterima)->sortByDesc('created_at');
        
        return view('kadivmekanik.riwayat_work_order', compact('workOrders'));
    }

    /**
     * Store work order.
     */
    public function storeWorkOrder(Request $request)
    {
        $request->validate([
            'divisi_pengaju' => 'required|string',
            'ditujukan' => 'required|string',
            'id_jenis_wo' => 'required|exists:jenis_work_order,id_jenis_wo',
            'tanggal' => 'required|date',
            'unit' => 'required|string',
            'uraian' => 'required|string',
            'dokumentasi' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // Validasi: Divisi lain tidak boleh mengajukan ke Admin
        $adminDivisi = DB::table('divisi')->where('nama_divisi', 'Administrator')->first();
        if ($adminDivisi && $request->ditujukan === 'Administrator') {
            return redirect()->back()
                ->with('error', 'Tidak dapat mengajukan work order ke Administrator. Administrator hanya berfungsi untuk memantau dan mengelola data.');
        }

        try {
            // Handle file upload
            $dokumentasiPath = null;
            if ($request->hasFile('dokumentasi')) {
                $dokumentasiPath = $request->file('dokumentasi')->store('work-orders', 'public');
            }

            // Get unit ID
            $unitId = 1; // Default
            $selectedUnit = Unit::where('nama_unit', $request->unit)->first();
            if ($selectedUnit) {
                $unitId = $selectedUnit->id_unit;
            }

            // Generate work order number
            $workOrderNumber = $this->generateWorkOrderNumber();

            // Simpan ke database
            SuratPengajuan::create([
                'no_surat_pengajuan' => $workOrderNumber,
                'ditujukan' => $request->ditujukan,
                'id_jenis_wo' => $request->id_jenis_wo,
                'tanggal' => $request->tanggal,
                'divisi_pengaju' => $request->divisi_pengaju,
                'unit' => $request->unit,
                'uraian' => $request->uraian,
                'dokumentasi' => $dokumentasiPath,
                'id_divisi' => session('user_divisi', 1),
                'id_peran' => 2, // KadivMekanik role
                'id_verifikator' => 1, // Default status
                'id_akun' => session('user_id', 1),
                'id_unit' => $unitId
            ]);

            return redirect()->route('kadivmekanik.work-order', ['from' => 'crud'])->with('success', 'Data berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->route('kadivmekanik.work-order')->with('error', 'Gagal membuat work order: ' . $e->getMessage());
        }
    }

    /**
     * Show work order.
     */
    public function showWorkOrder($id)
    {
        $workOrder = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder'])->findOrFail($id);
        
        // Generate URL untuk dokumentasi jika ada
        $dokumentasiUrl = null;
        if ($workOrder->dokumentasi) {
            // Gunakan asset() helper untuk generate URL relatif yang kompatibel dengan Laragon
            $dokumentasiUrl = asset('storage/' . $workOrder->dokumentasi);
        }
        
        $data = [
            'id_surat_pengajuan' => $workOrder->id_surat_pengajuan,
            'no_surat_pengajuan' => $workOrder->no_surat_pengajuan,
            'no_work_order' => $workOrder->no_surat_pengajuan,
            'divisi_pengaju' => $workOrder->divisi_pengaju,
            'ditujukan' => $workOrder->ditujukan,
            'id_jenis_wo' => $workOrder->id_jenis_wo,
            'jenis_wo' => $workOrder->jenisWorkOrder ? $workOrder->jenisWorkOrder->nama_jenis_wo : null,
            'tanggal' => $workOrder->tanggal,
            'unit' => $workOrder->unit,
            'id_unit' => $workOrder->id_unit,
            'uraian' => $workOrder->uraian,
            'dokumentasi' => $workOrder->dokumentasi,
            'dokumentasi_url' => $dokumentasiUrl, // URL lengkap untuk akses file
            'status' => $workOrder->verifikator ? $workOrder->verifikator->nama_status : ($workOrder->status ?? 'Menunggu'),
            'id_verifikator' => $workOrder->id_verifikator,
        ];

        return response()->json($data);
    }

    /**
     * Update work order.
     */
    public function updateWorkOrder(Request $request, $id)
    {
        $request->validate([
            'divisi_pengaju' => 'required|string',
            'ditujukan' => 'required|string',
            'id_jenis_wo' => 'required|exists:jenis_work_order,id_jenis_wo',
            'tanggal' => 'required|date',
            'unit' => 'required|string',
            'uraian' => 'required|string',
            'dokumentasi' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        try {
            $workOrder = SuratPengajuan::findOrFail($id);
            
            // Validasi: tidak bisa edit jika sudah disetujui atau ditolak
            if (in_array($workOrder->id_verifikator, [2, 3])) {
                return redirect()->back()
                    ->with('error', 'Work Order tidak dapat diedit karena sudah disetujui atau ditolak.');
            }

            // Handle file upload if new file is provided
            $dokumentasiPath = $workOrder->dokumentasi;
            if ($request->hasFile('dokumentasi')) {
                // Delete old file if exists
                if ($workOrder->dokumentasi && Storage::disk('public')->exists($workOrder->dokumentasi)) {
                    Storage::disk('public')->delete($workOrder->dokumentasi);
                }
                $dokumentasiPath = $request->file('dokumentasi')->store('work-orders', 'public');
            }

            // Get unit ID
            $unitId = 1; // Default
            $selectedUnit = Unit::where('nama_unit', $request->unit)->first();
            if ($selectedUnit) {
                $unitId = $selectedUnit->id_unit;
            }

            // Update database
            $workOrder->update([
                'ditujukan' => $request->ditujukan,
                'id_jenis_wo' => $request->id_jenis_wo,
                'tanggal' => $request->tanggal,
                'divisi_pengaju' => $request->divisi_pengaju,
                'unit' => $request->unit,
                'uraian' => $request->uraian,
                'dokumentasi' => $dokumentasiPath,
                'id_unit' => $unitId
            ]);

            return redirect()->route('kadivmekanik.work-order', ['from' => 'crud'])->with('success', 'Data berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->route('kadivmekanik.work-order')->with('error', 'Gagal mengupdate work order: ' . $e->getMessage());
        }
    }

    /**
     * Delete work order.
     */
    public function hapusWorkOrder($id)
    {
        try {
            $workOrder = SuratPengajuan::findOrFail($id);
            
            // Validasi: tidak bisa hapus jika sudah disetujui atau ditolak
            if (in_array($workOrder->id_verifikator, [2, 3])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Work Order tidak dapat dihapus karena sudah disetujui atau ditolak.'
                ], 403);
            }

            // Delete file if exists
            if ($workOrder->dokumentasi && Storage::disk('public')->exists($workOrder->dokumentasi)) {
                Storage::disk('public')->delete($workOrder->dokumentasi);
            }

            // Delete from database
            $workOrder->delete();

            // Simpan success message di session sebelum return JSON
            session()->flash('success', 'Data berhasil dihapus!');
            session()->flash('from_crud', true);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus!',
                'redirect' => route('kadivmekanik.work-order')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve work order.
     */
    public function approveWorkOrder($id)
    {
        try {
            $workOrder = SuratPengajuan::findOrFail($id);
            $userDivisi = Session::get('user_divisi');
            $userDivisiNama = DB::table('divisi')->where('id_divisi', $userDivisi)->value('nama_divisi');
            
            // Hanya divisi yang dituju yang bisa approve
            if ($workOrder->ditujukan !== $userDivisiNama) {
                Session::flash('error', 'Anda tidak memiliki akses untuk menyetujui work order ini.');
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menyetujui work order ini.',
                    'redirect' => route('kadivmekanik.daftar-pengajuan-work-order', ['from' => 'crud'])
                ], 403);
            }
            
            $workOrder->update([
                'id_verifikator' => 2, // 2 = Disetujui
                'status' => 'Disetujui'
            ]);
            
            // Set session message untuk notifikasi toast
            Session::flash('success', 'Work Order berhasil disetujui!');
            
            // Return JSON untuk AJAX dengan redirect URL yang sudah include from=crud
            $redirectUrl = route('kadivmekanik.daftar-pengajuan-work-order', ['from' => 'crud']);
            
            return response()->json([
                'success' => true,
                'message' => 'Work Order berhasil disetujui!',
                'redirect' => $redirectUrl
            ]);
        } catch (\Exception $e) {
            Session::flash('error', 'Gagal menyetujui work order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui work order: ' . $e->getMessage(),
                'redirect' => route('kadivmekanik.daftar-pengajuan-work-order', ['from' => 'crud'])
            ], 500);
        }
    }

    /**
     * Reject work order.
     */
    public function rejectWorkOrder($id)
    {
        try {
            $workOrder = SuratPengajuan::findOrFail($id);
            $userDivisi = Session::get('user_divisi');
            $userDivisiNama = DB::table('divisi')->where('id_divisi', $userDivisi)->value('nama_divisi');
            
            // Hanya divisi yang dituju yang bisa reject
            if ($workOrder->ditujukan !== $userDivisiNama) {
                Session::flash('error', 'Anda tidak memiliki akses untuk menolak work order ini.');
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menolak work order ini.',
                    'redirect' => route('kadivmekanik.daftar-pengajuan-work-order', ['from' => 'crud'])
                ], 403);
            }
            
            $workOrder->update([
                'id_verifikator' => 3, // 3 = Ditolak
                'status' => 'Ditolak'
            ]);
            
            // Set session message untuk notifikasi toast
            Session::flash('success', 'Work Order berhasil ditolak!');
            
            // Return JSON untuk AJAX dengan redirect URL yang sudah include from=crud
            $redirectUrl = route('kadivmekanik.daftar-pengajuan-work-order', ['from' => 'crud']);
            
            return response()->json([
                'success' => true,
                'message' => 'Work Order berhasil ditolak!',
                'redirect' => $redirectUrl
            ]);
        } catch (\Exception $e) {
            Session::flash('error', 'Gagal menolak work order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak work order: ' . $e->getMessage(),
                'redirect' => route('kadivmekanik.daftar-pengajuan-work-order', ['from' => 'crud'])
            ], 500);
        }
    }

    /**
     * Filter work order.
     */
    public function filterWorkOrder(Request $request)
    {
        try {
            $query = SuratPengajuan::with(['divisi', 'unit', 'akun', 'jenisWorkOrder']);

            // Apply filters if provided
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }
            if ($request->has('divisi') && $request->divisi) {
                $query->where('divisi_pengaju', $request->divisi);
            }
            if ($request->has('tanggal_mulai') && $request->tanggal_mulai) {
                $query->where('tanggal', '>=', $request->tanggal_mulai);
            }
            if ($request->has('tanggal_selesai') && $request->tanggal_selesai) {
                $query->where('tanggal', '<=', $request->tanggal_selesai);
            }

            $data = $query->orderBy('created_at', 'desc')->get()
                ->map(function ($item) {
                    return (object)[
                        'id' => $item->id_surat_pengajuan,
                        'id_surat_pengajuan' => $item->id_surat_pengajuan,
                        'no_surat_pengajuan' => $item->no_surat_pengajuan,
                        'no_work_order' => $item->no_surat_pengajuan,
                        'divisi_pengaju' => $item->divisi_pengaju,
                        'ditujukan' => $item->ditujukan,
                        'tanggal' => $item->tanggal,
                        'unit_code' => $item->unit ?: '',
                        'uraian' => $item->uraian,
                        'dokumentasi' => $item->dokumentasi,
                        'status' => $item->status ?? 'Menunggu',
                        'divisi_nama' => $item->divisi->nama_divisi ?? '',
                        'unit_nama' => $item->getRelation('unit') ? $item->getRelation('unit')->nama_unit : '',
                        'akun_email' => $item->akun->email ?? '',
                        'jenisWorkOrder' => $item->jenisWorkOrder
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memfilter data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search work order.
     */
    public function searchWorkOrder(Request $request)
    {
        try {
            $searchTerm = $request->get('search', '');
            
            $query = SuratPengajuan::with(['divisi', 'unit', 'akun', 'jenisWorkOrder'])
                ->where(function($q) use ($searchTerm) {
                    $q->where('no_surat_pengajuan', 'like', "%{$searchTerm}%")
                      ->orWhere('divisi_pengaju', 'like', "%{$searchTerm}%")
                      ->orWhere('ditujukan', 'like', "%{$searchTerm}%")
                      ->orWhere('unit', 'like', "%{$searchTerm}%")
                      ->orWhere('uraian', 'like', "%{$searchTerm}%");
                });

            $data = $query->orderBy('created_at', 'desc')->get()
                ->map(function ($item) {
                    return (object)[
                        'id' => $item->id_surat_pengajuan,
                        'id_surat_pengajuan' => $item->id_surat_pengajuan,
                        'no_surat_pengajuan' => $item->no_surat_pengajuan,
                        'no_work_order' => $item->no_surat_pengajuan,
                        'divisi_pengaju' => $item->divisi_pengaju,
                        'ditujukan' => $item->ditujukan,
                        'tanggal' => $item->tanggal,
                        'unit_code' => $item->unit ?: '',
                        'uraian' => $item->uraian,
                        'dokumentasi' => $item->dokumentasi,
                        'status' => $item->status ?? 'Menunggu',
                        'divisi_nama' => $item->divisi->nama_divisi ?? '',
                        'unit_nama' => $item->getRelation('unit') ? $item->getRelation('unit')->nama_unit : '',
                        'akun_email' => $item->akun->email ?? '',
                        'jenisWorkOrder' => $item->jenisWorkOrder
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencari data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export work order.
     */
    public function exportWorkOrder(Request $request)
    {
        try {
            $query = SuratPengajuan::with(['divisi', 'unit', 'akun']);

            // Apply filters if provided
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }
            if ($request->has('divisi') && $request->divisi) {
                $query->where('divisi_pengaju', $request->divisi);
            }
            if ($request->has('tanggal_mulai') && $request->tanggal_mulai) {
                $query->where('tanggal', '>=', $request->tanggal_mulai);
            }
            if ($request->has('tanggal_selesai') && $request->tanggal_selesai) {
                $query->where('tanggal', '<=', $request->tanggal_selesai);
            }

            $data = $query->orderBy('created_at', 'desc')->get();

            // Generate CSV content
            $csvContent = "No,No Work Order,Divisi Pengaju,Ditujukan,Tanggal,Unit,Uraian,Status,Dokumentasi\n";
            
            foreach ($data as $index => $item) {
                $csvContent .= sprintf(
                    "%d,%s,%s,%s,%s,%s,%s,%s,%s\n",
                    $index + 1,
                    $item->no_surat_pengajuan,
                    $item->divisi_pengaju,
                    $item->ditujukan,
                    $item->tanggal,
                    $item->unit,
                    $item->uraian,
                    $item->status ?? 'Menunggu',
                    $item->dokumentasi ?: '-'
                );
            }

            $filename = 'work_order_export_' . date('Y-m-d_H-i-s') . '.csv';
            
            return response($csvContent)
                ->header('Content-Type', 'text/csv')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
                
        } catch (\Exception $e) {
            return redirect()->route('kadivmekanik.work-order')->with('error', 'Gagal mengexport data: ' . $e->getMessage());
        }
    }

    /**
     * Display laporan harian mekanik page.
     */
    public function laporanHarianMekanik(Request $request)
    {
        $query = LaporanHarianMekanik::with(['unit', 'akun.karyawan']);

        // Apply filters if provided
        if ($request->has('tahun') && $request->tahun) {
            $query->whereYear('tanggal', $request->tahun);
        }
        if ($request->has('bulan') && $request->bulan) {
            $query->whereMonth('tanggal', $request->bulan);
        }
        if ($request->has('minggu') && $request->minggu && $request->minggu !== '' && $request->minggu !== 'semua') {
            $tahun = $request->tahun ?: Carbon::now('Asia/Makassar')->year;
            $bulan = $request->bulan ?: Carbon::now('Asia/Makassar')->month;
            
            // Calculate week range for real calendar weeks
            if (is_numeric($request->minggu)) {
                $weekNumber = (int)$request->minggu;
                $weekRange = $this->calculateWeekRange($tahun, $bulan, $weekNumber);
                
                if ($weekRange) {
                    $query->whereBetween('tanggal', [
                        $weekRange['start'],
                        $weekRange['end']
                    ]);
                } else {
                    // If week is beyond the month, return no data
                    $query->where('tanggal', '>', '9999-12-31');
                }
            }
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        // Get filter options
        $tahunOptions = LaporanHarianMekanik::selectRaw('YEAR(tanggal) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        $bulanOptions = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        // Get unit options for dropdown
        $unitOptions = Unit::all();
        $divisiOptions = Divisi::all();

        return view('kadivmekanik.laporan_harian_mekanik', compact('data', 'tahunOptions', 'bulanOptions', 'unitOptions', 'divisiOptions'));
    }

    /**
     * Display laporan pemakaian barang page.
     */
    public function laporanPemakaianBarang(Request $request)
    {
        $query = LaporanPemakaianBarang::with(['divisi', 'unit', 'akun.karyawan']);

        // Apply filters if provided
        if ($request->has('tahun') && $request->tahun) {
            $query->whereYear('tanggal', $request->tahun);
        }
        if ($request->has('bulan') && $request->bulan) {
            $query->whereMonth('tanggal', $request->bulan);
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        // Get filter options
        $tahunOptions = LaporanPemakaianBarang::selectRaw('YEAR(tanggal) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        $bulanOptions = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        // Get unit options for dropdown
        $unitOptions = Unit::all();
        $divisiOptions = Divisi::all();

        return view('kadivmekanik.laporan_pemakaian_barang', compact('data', 'tahunOptions', 'bulanOptions', 'unitOptions', 'divisiOptions'));
    }

    /**
     * Filter laporan harian mekanik.
     */
    public function filterLaporanHarianMekanik(Request $request)
    {
        $query = LaporanHarianMekanik::with(['divisi', 'unit', 'akun.karyawan']);

        // Apply filters
        if ($request->has('tahun') && $request->tahun && $request->tahun !== '') {
            $query->whereYear('tanggal', $request->tahun);
        }
        if ($request->has('bulan') && $request->bulan && $request->bulan !== '') {
            $query->whereMonth('tanggal', $request->bulan);
        }
        if ($request->has('minggu') && $request->minggu && $request->minggu !== '' && $request->minggu !== 'semua') {
            // Gunakan tahun dan bulan dari request, atau default ke tahun/bulan saat ini
            $tahun = $request->has('tahun') && $request->tahun && $request->tahun !== '' 
                ? (int)$request->tahun 
                : Carbon::now('Asia/Makassar')->year;
            $bulan = $request->has('bulan') && $request->bulan && $request->bulan !== '' 
                ? (int)$request->bulan 
                : Carbon::now('Asia/Makassar')->month;
            
            // Calculate week range for real calendar weeks
            if (is_numeric($request->minggu)) {
                $weekNumber = (int)$request->minggu;
                $weekRange = $this->calculateWeekRange($tahun, $bulan, $weekNumber);
                
                if ($weekRange) {
                    $query->whereBetween('tanggal', [
                        $weekRange['start'],
                        $weekRange['end']
                    ]);
                } else {
                    // If week is beyond the month, return no data
                    $query->where('tanggal', '>', '9999-12-31');
                }
            }
        }

        $data = $query->orderBy('tanggal', 'desc')->orderBy('created_at', 'desc')->get();

        // Format data untuk response
        $formattedData = $data->map(function($item) {
            return [
                'id_laporan_harian_mekanik' => $item->id_laporan_harian_mekanik,
                'tanggal' => $item->tanggal,
                'nama_unit' => $item->nama_unit,
                'keluhan_kerusakan' => $item->keluhan_kerusakan,
                'penyebab_kerusakan' => $item->penyebab_kerusakan,
                'tanggal_mulai' => $item->tanggal_mulai,
                'tanggal_selesai' => $item->tanggal_selesai,
                'tindakan_perbaikan' => $item->tindakan_perbaikan,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedData,
            'message' => 'Data berhasil difilter'
        ]);
    }

    /**
     * Filter laporan pemakaian barang.
     */
    public function filterLaporanPemakaianBarang(Request $request)
    {
        $query = LaporanPemakaianBarang::with(['divisi', 'unit', 'akun.karyawan']);

        // Apply filters
        if ($request->has('tahun') && $request->tahun && $request->tahun !== '') {
            $query->whereYear('tanggal', $request->tahun);
        }
        if ($request->has('bulan') && $request->bulan && $request->bulan !== '') {
            $query->whereMonth('tanggal', $request->bulan);
        }
        if ($request->has('minggu') && $request->minggu && $request->minggu !== '' && $request->minggu !== 'semua') {
            // Gunakan tahun dan bulan dari request, atau default ke tahun/bulan saat ini
            $tahun = $request->has('tahun') && $request->tahun && $request->tahun !== '' 
                ? (int)$request->tahun 
                : Carbon::now('Asia/Makassar')->year;
            $bulan = $request->has('bulan') && $request->bulan && $request->bulan !== '' 
                ? (int)$request->bulan 
                : Carbon::now('Asia/Makassar')->month;
            
            // Calculate week range for real calendar weeks
            if (is_numeric($request->minggu)) {
                $weekNumber = (int)$request->minggu;
                $weekRange = $this->calculateWeekRange($tahun, $bulan, $weekNumber);
                
                if ($weekRange) {
                    $query->whereBetween('tanggal', [
                        $weekRange['start'],
                        $weekRange['end']
                    ]);
                } else {
                    // If week is beyond the month, return no data
                    $query->where('tanggal', '>', '9999-12-31');
                }
            }
        }

        $data = $query->orderBy('tanggal', 'desc')->orderBy('created_at', 'desc')->get();

        // Format data untuk response
        $formattedData = $data->map(function($item) {
            return [
                'id_laporan_pemakaian_barang' => $item->id_laporan_pemakaian_barang,
                'tanggal' => $item->tanggal,
                'nama_barang' => $item->nama_barang,
                'kode_unit' => $item->kode_unit,
                'jumlah' => $item->jumlah,
                'bentuk_satuan' => $item->bentuk_satuan,
                'harga_satuan' => $item->harga_satuan,
                'total_harga' => $item->total_harga,
                'keterangan' => $item->keterangan,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedData,
            'message' => 'Data berhasil difilter'
        ]);
    }


    /**
     * Store laporan harian mekanik.
     */
    public function storeLaporanHarianMekanik(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'nama_unit' => 'required|string',
            'keluhan_kerusakan' => 'required|string',
            'penyebab_kerusakan' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date',
            'tindakan_perbaikan' => 'required|string',
        ]);

        // Get unit ID from selected unit name
        $unitId = 1; // Default
        $selectedUnit = Unit::where('nama_unit', $request->nama_unit)->first();
        if ($selectedUnit) {
            $unitId = $selectedUnit->id_unit;
        }

        // Simpan data ke database
        LaporanHarianMekanik::create([
            'tanggal' => $request->tanggal,
            'nama_unit' => $request->nama_unit,
            'keluhan_kerusakan' => $request->keluhan_kerusakan,
            'penyebab_kerusakan' => $request->penyebab_kerusakan,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'tindakan_perbaikan' => $request->tindakan_perbaikan,
            'id_divisi' => session('user_divisi', 1), // Divisi dari session user
            'id_unit' => $unitId,
            'id_akun' => session('user_id', 1), // ID akun dari session
        ]);

        return redirect()->route('kadivmekanik.laporan-harian-mekanik', ['from' => 'crud'])->with('success', 'Data berhasil ditambahkan');
    }

    /**
     * Show laporan harian mekanik.
     */
    public function showLaporanHarianMekanik($id)
    {
        $laporan = LaporanHarianMekanik::with(['divisi', 'unit', 'akun.karyawan'])->findOrFail($id);
        return response()->json($laporan);
    }

    /**
     * Show laporan pemakaian barang.
     */
    public function showLaporanPemakaianBarang($id)
    {
        $laporan = LaporanPemakaianBarang::with(['divisi', 'unit', 'akun.karyawan'])->findOrFail($id);
        return response()->json($laporan);
    }

    /**
     * Update laporan harian mekanik.
     */
    public function updateLaporanHarianMekanik(Request $request, $id)
    {
        $laporan = LaporanHarianMekanik::findOrFail($id);
        
        $request->validate([
            'tanggal' => 'required|date',
            'nama_unit' => 'required|string',
            'keluhan_kerusakan' => 'required|string',
            'penyebab_kerusakan' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date',
            'tindakan_perbaikan' => 'required|string',
        ]);

        $laporan->update($request->all());

        return redirect()->route('kadivmekanik.laporan-harian-mekanik', ['from' => 'crud'])->with('success', 'Data berhasil diperbarui');
    }

    /**
     * Destroy laporan harian mekanik.
     */
    public function destroyLaporanHarianMekanik($id)
    {
        try {
            $laporan = LaporanHarianMekanik::findOrFail($id);
            $laporan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus!',
                'redirect' => route('kadivmekanik.laporan-harian-mekanik')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store laporan pemakaian barang.
     */
    public function storeLaporanPemakaianBarang(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'kode_unit' => 'required|string',
            'nama_barang' => 'required|string',
            'jumlah' => 'required|numeric|min:1',
            'bentuk_satuan' => 'required|string',
            'harga_satuan' => 'required|numeric|min:0',
            'total_harga' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        // Get unit ID from selected unit code
        $unitId = 1; // Default
        $selectedUnit = Unit::where('kode_unit', $request->kode_unit)->first();
        if ($selectedUnit) {
            $unitId = $selectedUnit->id_unit;
        }

        // Simpan data ke database
        LaporanPemakaianBarang::create([
            'tanggal' => $request->tanggal,
            'kode_unit' => $request->kode_unit,
            'nama_barang' => $request->nama_barang,
            'jumlah' => $request->jumlah,
            'bentuk_satuan' => $request->bentuk_satuan,
            'harga_satuan' => $request->harga_satuan,
            'total_harga' => $request->total_harga,
            'keterangan' => $request->keterangan,
            'id_divisi' => session('user_divisi', 1), // Divisi dari session user
            'id_unit' => $unitId,
            'id_akun' => session('user_id', 1), // ID akun dari session
        ]);

        return redirect()->route('kadivmekanik.laporan-pemakaian-barang', ['from' => 'crud'])->with('success', 'Data berhasil ditambahkan');
    }

    /**
     * Update laporan pemakaian barang.
     */
    public function updateLaporanPemakaianBarang(Request $request, $id)
    {
        $laporan = LaporanPemakaianBarang::findOrFail($id);
        
        $request->validate([
            'tanggal' => 'required|date',
            'kode_unit' => 'required|string',
            'nama_barang' => 'required|string',
            'jumlah' => 'required|numeric|min:1',
            'bentuk_satuan' => 'required|string',
            'harga_satuan' => 'required|numeric|min:0',
            'total_harga' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $laporan->update($request->all());

        return redirect()->route('kadivmekanik.laporan-pemakaian-barang', ['from' => 'crud'])->with('success', 'Data berhasil diperbarui');
    }

    /**
     * Destroy laporan pemakaian barang.
     */
    public function destroyLaporanPemakaianBarang($id)
    {
        try {
            $laporan = LaporanPemakaianBarang::findOrFail($id);
            $laporan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus!',
                'redirect' => route('kadivmekanik.laporan-pemakaian-barang')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }




    /**
     * Download Excel laporan harian mekanik (khusus untuk KadivMekanik).
     */
    public function downloadExcelLaporanHarianMekanik(Request $request)
    {
        try {
            // Apply filters and get data
            $result = $this->applyFiltersAndGetData($request);
            $data = $result['data'];
            $periode = $result['periode'];
            
            $timestamp = date('Y-m-d_H-i-s');
            $filename = 'laporan_harian_mekanik_' . $periode . '_' . $timestamp . '.xlsx';

            // Bangun file Excel dengan PhpSpreadsheet agar tidak bergantung pada Facade Excel
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Header dokumen (sesuai preview)
            $sheet->setCellValue('A1', 'PT. KALIMANTAN CONCRETE ENGINEERING');
            $sheet->mergeCells('A1:G1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

            $sheet->setCellValue('A2', 'LAPORAN PERBAIKAN MEKANIK');
            $sheet->mergeCells('A2:G2');
            $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

            $sheet->setCellValue('A3', 'PERIODE: ' . $periode);
            $sheet->mergeCells('A3:G3');
            $sheet->getStyle('A3')->getFont()->setBold(true);
            $sheet->getStyle('A3')->getAlignment()->setHorizontal('center');

            // Sisipkan baris kosong sebelum header kolom
            $sheet->insertNewRowBefore(4, 1);

            // Header kolom
            $headers = ['Hari/Tanggal', 'Nama Unit', 'Keluhan/Kerusakan', 'Penyebab Kerusakan', 'Mulai Reparasi Hari/Tanggal', 'Selesai Reparasi Hari/Tanggal', 'Tindakan Perbaikan dari Mekanik'];
            $sheet->fromArray($headers, null, 'A5');
            $sheet->getStyle('A5:G5')->getFont()->setBold(true);
            $sheet->getStyle('A5:G5')->getAlignment()->setHorizontal('center');

            // Data baris - Group by tanggal seperti di preview
            $rowIndex = 6;
            $groupedData = $data->groupBy(function($item) {
                return \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d');
            });
            
            foreach ($groupedData as $date => $dayReports) {
                $carbon = \Carbon\Carbon::parse($date)->locale('id');
                $dateFormatted = $carbon->isoFormat('dddd, DD/MM/YYYY');
                $reports = $dayReports->toArray();
                $rowCount = count($reports);
                
                foreach ($reports as $index => $report) {
                    $mulaiReparasi = '';
                    if (!empty($report['tanggal_mulai'])) {
                        $mulai = \Carbon\Carbon::parse($report['tanggal_mulai'])->locale('id');
                        $mulaiReparasi = $mulai->isoFormat('dddd, DD/MM/YYYY');
                    }

                    $selesaiReparasi = '';
                    if (!empty($report['tanggal_selesai'])) {
                        $selesai = \Carbon\Carbon::parse($report['tanggal_selesai'])->locale('id');
                        $selesaiReparasi = $selesai->isoFormat('dddd, DD/MM/YYYY');
                    }

                    if ($index === 0) {
                        // First row - set tanggal
                        $sheet->setCellValue('A' . $rowIndex, $dateFormatted);
                        $sheet->setCellValue('B' . $rowIndex, $report['nama_unit'] ?? '');
                        $sheet->setCellValue('C' . $rowIndex, $report['keluhan_kerusakan'] ?? '');
                        $sheet->setCellValue('D' . $rowIndex, $report['penyebab_kerusakan'] ?? '');
                        $sheet->setCellValue('E' . $rowIndex, $mulaiReparasi);
                        $sheet->setCellValue('F' . $rowIndex, $selesaiReparasi);
                        $sheet->setCellValue('G' . $rowIndex, $report['tindakan_perbaikan'] ?? '');
                        
                        // Merge tanggal cell jika ada multiple entries
                        if ($rowCount > 1) {
                            $sheet->mergeCells('A' . $rowIndex . ':A' . ($rowIndex + $rowCount - 1));
                            $sheet->getStyle('A' . $rowIndex)->getAlignment()->setVertical('center')->setHorizontal('center');
                        }
                    } else {
                        // Subsequent rows for same date - no tanggal column
                        $sheet->setCellValue('B' . $rowIndex, $report['nama_unit'] ?? '');
                        $sheet->setCellValue('C' . $rowIndex, $report['keluhan_kerusakan'] ?? '');
                        $sheet->setCellValue('D' . $rowIndex, $report['penyebab_kerusakan'] ?? '');
                        $sheet->setCellValue('E' . $rowIndex, $mulaiReparasi);
                        $sheet->setCellValue('F' . $rowIndex, $selesaiReparasi);
                        $sheet->setCellValue('G' . $rowIndex, $report['tindakan_perbaikan'] ?? '');
                    }
                    
                    $rowIndex++;
                }
            }
            
            // Jika tidak ada data
            if ($data->count() === 0) {
                $sheet->setCellValue('A' . $rowIndex, 'Tidak ada data laporan untuk periode yang dipilih');
                $sheet->mergeCells('A' . $rowIndex . ':G' . $rowIndex);
                $sheet->getStyle('A' . $rowIndex)->getAlignment()->setHorizontal('center');
            }

            // Style untuk border dan alignment
            $lastRow = $rowIndex - 1;
            if ($data->count() > 0) {
                // Border untuk semua cell
                $sheet->getStyle('A5:G' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);
                
                // Padding/spacing untuk header
                $sheet->getStyle('A5:G5')->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F0F0F0'],
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    ],
                ]);
                
                // Padding untuk data rows
                $sheet->getStyle('A6:G' . $lastRow)->applyFromArray([
                    'alignment' => [
                        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP,
                        'wrapText' => true,
                    ],
                ]);
                
                // Center align untuk kolom tanggal
                $sheet->getStyle('A6:A' . $lastRow)->getAlignment()->setHorizontal('center')->setVertical('center');
                
                // Left align untuk kolom lainnya
                $sheet->getStyle('B6:G' . $lastRow)->getAlignment()->setHorizontal('left')->setVertical('top');
                $sheet->getStyle('B6:G' . $lastRow)->getAlignment()->setWrapText(true);
                
                // Set row height untuk spacing yang lebih baik
                $sheet->getRowDimension(5)->setRowHeight(25); // Header row
                for ($i = 6; $i <= $lastRow; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(-1); // Auto height dengan minimum
                }
            }
            
            // Set width untuk kolom dengan spacing yang lebih baik
            $sheet->getColumnDimension('A')->setWidth(22); // Hari/Tanggal
            $sheet->getColumnDimension('B')->setWidth(18); // Nama Unit
            $sheet->getColumnDimension('C')->setWidth(28); // Keluhan/Kerusakan
            $sheet->getColumnDimension('D')->setWidth(28); // Penyebab Kerusakan
            $sheet->getColumnDimension('E')->setWidth(28); // Mulai Reparasi
            $sheet->getColumnDimension('F')->setWidth(28); // Selesai Reparasi
            $sheet->getColumnDimension('G')->setWidth(35); // Tindakan Perbaikan
            
            // Set padding untuk semua cell (menggunakan indentasi)
            if ($data->count() > 0) {
                $sheet->getStyle('A5:G' . $lastRow)->getAlignment()->setIndent(1);
            }

            // Stream download
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            return response()->streamDownload(function() use ($writer) {
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);
                
        } catch (\Exception $e) {
            return redirect()->route('kadivmekanik.laporan-harian-mekanik')->with('error', 'Gagal mengexport data Excel: ' . $e->getMessage());
        }
    }

    /**
     * Helper method untuk apply filters dan generate periode
     */
    private function applyFiltersAndGetData($request)
    {
        $query = LaporanHarianMekanik::with(['divisi', 'unit', 'akun.karyawan']);

        // Apply filters if provided
        if ($request->has('tahun') && $request->tahun && $request->tahun !== '') {
            $query->whereYear('tanggal', $request->tahun);
        }
        if ($request->has('bulan') && $request->bulan && $request->bulan !== '') {
            $query->whereMonth('tanggal', $request->bulan);
        }
        if ($request->has('minggu') && $request->minggu && $request->minggu !== '' && $request->minggu !== 'semua') {
            $tahun = $request->tahun ?: Carbon::now('Asia/Makassar')->year;
            $bulan = $request->bulan ?: Carbon::now('Asia/Makassar')->month;
            
            // Calculate week range for real calendar weeks
            if (is_numeric($request->minggu)) {
                $weekNumber = (int)$request->minggu;
                $weekRange = $this->calculateWeekRange($tahun, $bulan, $weekNumber);
                
                if ($weekRange) {
                    $query->whereBetween('tanggal', [
                        $weekRange['start'],
                        $weekRange['end']
                    ]);
                } else {
                    // If week is beyond the month, return no data
                    $query->where('tanggal', '>', '9999-12-31');
                }
            }
        }

        $data = $query->orderBy('tanggal', 'desc')->orderBy('created_at', 'desc')->get();
        
        // Generate period string for filename and display - HANYA menampilkan apa yang dipilih user
        $periode = 'Semua Data';
        $bulanNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        // Ambil nilai filter - hanya yang benar-benar dipilih user
        $tahun = $request->has('tahun') && $request->tahun && $request->tahun !== '' 
            ? $request->tahun 
            : null;
        $bulan = $request->has('bulan') && $request->bulan && $request->bulan !== '' 
            ? (int)$request->bulan 
            : null;
        $minggu = $request->has('minggu') && $request->minggu && $request->minggu !== '' && $request->minggu !== 'semua'
            ? (int)$request->minggu 
            : null;
        
        // Build periode berdasarkan apa yang dipilih user (tanpa default values)
        $periodeParts = [];
        
        if ($minggu !== null) {
            $periodeParts[] = 'Minggu ' . $minggu;
        }
        
        if ($bulan !== null) {
            $periodeParts[] = $bulanNames[$bulan];
        }
        
        if ($tahun !== null) {
            $periodeParts[] = $tahun;
        }
        
        // Gabungkan bagian-bagian periode
        if (count($periodeParts) > 0) {
            $periode = implode(' ', $periodeParts);
        } else {
            $periode = 'Semua Data';
        }

        return compact('data', 'periode');
    }

    /**
     * Preview laporan harian mekanik (khusus untuk KadivMekanik).
     */
    public function previewLaporanHarianMekanik(Request $request)
    {
        try {
            $result = $this->applyFiltersAndGetData($request);
            $data = $result['data'];
            $periode = $result['periode'];
            
            $format = 'preview';
            return view('export.laporan_harian_mekanik', compact('data', 'periode', 'format'));

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat preview: ' . $e->getMessage());
        }
    }

    /**
     * Download PDF laporan harian mekanik (khusus untuk KadivMekanik).
     */
    public function downloadPdfLaporanHarianMekanik(Request $request)
    {
        try {
            $result = $this->applyFiltersAndGetData($request);
            $data = $result['data'];
            $periode = $result['periode'];
            
            $timestamp = date('Y-m-d_H-i-s');
            $filename = 'laporan_harian_mekanik_' . $periode . '_' . $timestamp . '.pdf';
            
            // Export to PDF - ensure 1 page
            $format = 'pdf';
            
            // Load view dengan format PDF
            $html = view('export.laporan_harian_mekanik', compact('data', 'periode', 'format'))->render();
            
            // Remove external CSS links yang bisa menyebabkan error
            $html = preg_replace('/<link[^>]*href=["\'][^"\']*font-awesome[^"\']*["\'][^>]*>/i', '', $html);
            $html = preg_replace('/<link[^>]*href=["\'][^"\']*bootstrap[^"\']*["\'][^>]*>/i', '', $html);
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'landscape');
            $pdf->setOption('enable-smart-shrinking', true);
            $pdf->setOption('shrink-to-fit', true);
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isRemoteEnabled', false);
            $pdf->setOption('isFontSubsettingEnabled', true);
            
            return $pdf->download($filename);
                
        } catch (\Exception $e) {
            return redirect()->route('kadivmekanik.laporan-harian-mekanik')->with('error', 'Gagal mengexport data PDF: ' . $e->getMessage());
        }
    }

    /**
     * Export laporan pemakaian barang.
     */
    public function exportLaporanPemakaianBarang(Request $request)
    {
        try {
            $query = LaporanPemakaianBarang::with(['divisi', 'unit', 'akun.karyawan']);

            // Apply filters if provided
            if ($request->has('tahun') && $request->tahun) {
                $query->whereYear('tanggal', $request->tahun);
            }
            if ($request->has('bulan') && $request->bulan) {
                $query->whereMonth('tanggal', $request->bulan);
            }

            $data = $query->orderBy('created_at', 'desc')->get();
            
            return Excel::download(new LaporanPemakaianBarangExport($data), 'laporan_pemakaian_barang_' . date('Y-m-d_H-i-s') . '.xlsx');
                
        } catch (\Exception $e) {
            return redirect()->route('kadivmekanik.laporan-pemakaian-barang')->with('error', 'Gagal mengexport data: ' . $e->getMessage());
        }
    }

    /**
     * Apply filters and get data for laporan pemakaian barang (similar to applyFiltersAndGetData).
     */
    private function applyFiltersAndGetDataPemakaianBarang($request)
    {
        $query = LaporanPemakaianBarang::with(['divisi', 'unit', 'akun.karyawan']);

        // Apply filters if provided
        if ($request->has('tahun') && $request->tahun && $request->tahun !== '') {
            $query->whereYear('tanggal', $request->tahun);
        }
        if ($request->has('bulan') && $request->bulan && $request->bulan !== '') {
            $query->whereMonth('tanggal', $request->bulan);
        }
        if ($request->has('minggu') && $request->minggu && $request->minggu !== '' && $request->minggu !== 'semua') {
            $tahun = $request->tahun ?: Carbon::now('Asia/Makassar')->year;
            $bulan = $request->bulan ?: Carbon::now('Asia/Makassar')->month;
            
            // Calculate week range for real calendar weeks
            if (is_numeric($request->minggu)) {
                $weekNumber = (int)$request->minggu;
                $weekRange = $this->calculateWeekRange($tahun, $bulan, $weekNumber);
                
                if ($weekRange) {
                    $query->whereBetween('tanggal', [
                        $weekRange['start'],
                        $weekRange['end']
                    ]);
                } else {
                    // If week is beyond the month, return no data
                    $query->where('tanggal', '>', '9999-12-31');
                }
            }
        }

        $data = $query->orderBy('tanggal', 'desc')->orderBy('created_at', 'desc')->get();
        
        // Generate period string for filename and display - HANYA menampilkan apa yang dipilih user
        $periode = 'Semua Data';
        $bulanNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        // Ambil nilai filter - hanya yang benar-benar dipilih user
        $tahun = $request->has('tahun') && $request->tahun && $request->tahun !== '' 
            ? $request->tahun 
            : null;
        $bulan = $request->has('bulan') && $request->bulan && $request->bulan !== '' 
            ? (int)$request->bulan 
            : null;
        $minggu = $request->has('minggu') && $request->minggu && $request->minggu !== '' && $request->minggu !== 'semua'
            ? (int)$request->minggu 
            : null;
        
        // Build periode berdasarkan apa yang dipilih user (tanpa default values)
        $periodeParts = [];
        
        if ($minggu !== null) {
            $periodeParts[] = 'Minggu ' . $minggu;
        }
        
        if ($bulan !== null) {
            $periodeParts[] = $bulanNames[$bulan];
        }
        
        if ($tahun !== null) {
            $periodeParts[] = $tahun;
        }
        
        // Gabungkan bagian-bagian periode
        if (count($periodeParts) > 0) {
            $periode = implode(' ', $periodeParts);
        } else {
            $periode = 'Semua Data';
        }

        return compact('data', 'periode');
    }

    /**
     * Preview laporan pemakaian barang.
     */
    public function previewLaporanPemakaianBarang(Request $request)
    {
        try {
            $result = $this->applyFiltersAndGetDataPemakaianBarang($request);
            $data = $result['data'];
            $periode = $result['periode'];
            
            $format = $request->get('format', 'preview');
            return view('export.laporan_pemakaian_barang', compact('data', 'periode', 'format'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat preview: ' . $e->getMessage());
        }
    }

    /**
     * Download PDF laporan pemakaian barang.
     */
    public function downloadPdfLaporanPemakaianBarang(Request $request)
    {
        try {
            $result = $this->applyFiltersAndGetDataPemakaianBarang($request);
            $data = $result['data'];
            $periode = $result['periode'];
            
            $timestamp = date('Y-m-d_H-i-s');
            // Replace spaces with underscores for filename
            $periodeFilename = str_replace(' ', '_', $periode);
            $filename = 'laporan_pemakaian_barang_' . $periodeFilename . '_' . $timestamp . '.pdf';

            // Export to PDF - ensure 1 page
            $format = 'pdf';
            
            // Load view dengan format PDF
            $html = view('export.laporan_pemakaian_barang', compact('data', 'periode', 'format'))->render();
            
            // Remove external CSS links yang bisa menyebabkan error
            $html = preg_replace('/<link[^>]*href=["\'][^"\']*font-awesome[^"\']*["\'][^>]*>/i', '', $html);
            $html = preg_replace('/<link[^>]*href=["\'][^"\']*bootstrap[^"\']*["\'][^>]*>/i', '', $html);
            
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'landscape');
            $pdf->setOption('enable-smart-shrinking', true);
            $pdf->setOption('shrink-to-fit', true);
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isRemoteEnabled', false);
            $pdf->setOption('isFontSubsettingEnabled', true);
            $pdf->setOption('dpi', 96);
            $pdf->setOption('defaultFont', 'Arial');

            return $pdf->download($filename);
        } catch (\Exception $e) {
            return redirect()->route('kadivmekanik.laporan-pemakaian-barang')->with('error', 'Gagal mengexport PDF: ' . $e->getMessage());
        }
    }

    /**
     * Download Excel laporan pemakaian barang.
     */
    public function downloadExcelLaporanPemakaianBarang(Request $request)
    {
        try {
            $result = $this->applyFiltersAndGetDataPemakaianBarang($request);
            $data = $result['data'];
            $periode = $result['periode'];

            // Bangun file Excel dengan PhpSpreadsheet agar tidak bergantung pada Facade Excel
            $timestamp = date('Y-m-d_H-i-s');
            // Replace spaces with underscores for filename
            $periodeFilename = str_replace(' ', '_', $periode);
            $filename = 'laporan_pemakaian_barang_' . $periodeFilename . '_' . $timestamp . '.xlsx';

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Header dokumen (sesuai preview)
            $sheet->setCellValue('A1', 'PT. KALIMANTAN CONCRETE ENGINEERING');
            $sheet->mergeCells('A1:I1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

            $sheet->setCellValue('A2', 'LAPORAN PEMAKAIAN BARANG');
            $sheet->mergeCells('A2:I2');
            $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

            $sheet->setCellValue('A3', 'PERIODE: ' . $periode);
            $sheet->mergeCells('A3:I3');
            $sheet->getStyle('A3')->getFont()->setBold(true);
            $sheet->getStyle('A3')->getAlignment()->setHorizontal('center');

            // Sisipkan baris kosong sebelum header kolom
            $sheet->insertNewRowBefore(4, 1);

            // Header kolom (sesuai preview tabel)
            $headers = ['No', 'Tanggal', 'Sparepart/Material/Jasa', 'Kode Unit', 'Jumlah', 'Bentuk Satuan', 'Harga Satuan', 'Total Harga', 'Keterangan'];
            $sheet->fromArray($headers, null, 'A5');
            $sheet->getStyle('A5:I5')->getFont()->setBold(true);
            $sheet->getStyle('A5:I5')->getAlignment()->setHorizontal('center');

            // Data baris
            $rowIndex = 6;
            $no = 1;
            foreach ($data as $row) {
                $tanggal = $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') : '';
                $hargaSatuan = (float)($row->harga_satuan ?? 0);
                $total = isset($row->total_harga) && $row->total_harga !== null && $row->total_harga !== ''
                    ? (float)$row->total_harga
                    : ((float)($row->jumlah ?? 0) * $hargaSatuan);

                $sheet->fromArray([
                    $no,
                    $tanggal,
                    $row->nama_barang ?? '-',
                    $row->kode_unit ?? '-',
                    (float)($row->jumlah ?? 0),
                    $row->bentuk_satuan ?? '-',
                    $hargaSatuan,
                    $total,
                    $row->keterangan ?? '-',
                ], null, 'A' . $rowIndex);

                $rowIndex++;
                $no++;
            }

            // Format angka untuk harga
            $highestRow = $sheet->getHighestRow();
            if ($highestRow >= 6) {
                $sheet->getStyle('G6:G' . $highestRow)->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle('H6:H' . $highestRow)->getNumberFormat()->setFormatCode('#,##0');

                // Baris total (JUMLAH) seperti preview
                $totalRow = $highestRow + 1;
                $sheet->mergeCells('A' . $totalRow . ':F' . $totalRow);
                $sheet->setCellValue('G' . $totalRow, 'JUMLAH');
                $sheet->getStyle('G' . $totalRow)->getFont()->setBold(true);
                $sheet->setCellValue('H' . $totalRow, '=SUM(H6:H' . $highestRow . ')');
                $sheet->getStyle('H' . $totalRow)->getFont()->setBold(true);
                $sheet->getStyle('H' . $totalRow)->getNumberFormat()->setFormatCode('#,##0');
            }

            // Auto-size kolom
            foreach (range('A', 'I') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Stream download
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            return response()->streamDownload(function() use ($writer) {
                $writer->save('php://output');
            }, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ]);
        } catch (\Exception $e) {
            return redirect()->route('kadivmekanik.laporan-pemakaian-barang')->with('error', 'Gagal mengexport Excel: ' . $e->getMessage());
        }
    }

    /**
     * Print laporan harian mekanik (PDF).
     */
    public function printLaporanHarianMekanik(Request $request)
    {
        try {
            $query = LaporanHarianMekanik::with(['divisi', 'unit', 'akun.karyawan']);

            // Apply filters if provided
            if ($request->has('tahun') && $request->tahun) {
                $query->whereYear('tanggal', $request->tahun);
            }
            if ($request->has('bulan') && $request->bulan) {
                $query->whereMonth('tanggal', $request->bulan);
            }

            $data = $query->orderBy('created_at', 'desc')->get();
            
            // Generate period string for PDF
            $periode = 'Semua Data';
            if ($request->has('tahun') && $request->tahun) {
                $periode = 'Tahun ' . $request->tahun;
                if ($request->has('bulan') && $request->bulan) {
                    $bulanNames = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ];
                    $periode = $bulanNames[$request->bulan] . ' ' . $request->tahun;
                }
            }

            $pdf = Pdf::loadView('pdf.laporan_harian_mekanik', compact('data', 'periode'));
            $pdf->setPaper('A4', 'landscape');
            
            return $pdf->download('laporan_harian_mekanik_' . date('Y-m-d_H-i-s') . '.pdf');
        } catch (\Exception $e) {
            return redirect()->route('kadivmekanik.laporan-harian-mekanik')->with('error', 'Gagal mencetak laporan: ' . $e->getMessage());
        }
    }

    /**
     * Print laporan pemakaian barang (PDF).
     */
    public function printLaporanPemakaianBarang(Request $request)
    {
        try {
            $query = LaporanPemakaianBarang::with(['divisi', 'unit', 'akun.karyawan']);

            // Apply filters if provided
            if ($request->has('tahun') && $request->tahun) {
                $query->whereYear('tanggal', $request->tahun);
            }
            if ($request->has('bulan') && $request->bulan) {
                $query->whereMonth('tanggal', $request->bulan);
            }

            $data = $query->orderBy('created_at', 'desc')->get();
            
            // Generate period string for PDF
            $periode = 'Semua Data';
            if ($request->has('tahun') && $request->tahun) {
                $periode = 'Tahun ' . $request->tahun;
                if ($request->has('bulan') && $request->bulan) {
                    $bulanNames = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ];
                    $periode = $bulanNames[$request->bulan] . ' ' . $request->tahun;
                }
            }

            $pdf = Pdf::loadView('pdf.laporan_pemakaian_barang', compact('data', 'periode'));
            $pdf->setPaper('A4', 'landscape');
            
            return $pdf->download('laporan_pemakaian_barang_' . date('Y-m-d_H-i-s') . '.pdf');
        } catch (\Exception $e) {
            return redirect()->route('kadivmekanik.laporan-pemakaian-barang')->with('error', 'Gagal mencetak laporan: ' . $e->getMessage());
        }
    }

    /**
     * Print laporan pemakaian barang (single item).
     */
    public function printLaporanPemakaianBarangSingle($id)
    {
        try {
            $laporan = LaporanPemakaianBarang::with(['divisi', 'unit', 'akun.karyawan'])->findOrFail($id);
            
            return view('kadivmekanik.print_laporan_pemakaian_barang', compact('laporan'));
        } catch (\Exception $e) {
            return redirect()->route('kadivmekanik.laporan-pemakaian-barang')->with('error', 'Gagal mencetak laporan: ' . $e->getMessage());
        }
    }

    /**
     * Display profile page.
     */
    public function profile()
    {
        $user = DB::table('akun')
            ->join('karyawan', 'akun.id_karyawan', '=', 'karyawan.id_karyawan')
            ->join('divisi', 'akun.id_divisi', '=', 'divisi.id_divisi')
            ->where('akun.id_akun', session('user_id'))
            ->select('akun.*', 'karyawan.*', 'divisi.nama_divisi')
            ->first();

        return view('kadivmekanik.profile', compact('user'));
    }

    /**
     * Update profile.
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'no_hp' => 'required|string|max:20',
            'alamat' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
        ]);

        try {
            $userId = session('user_id');
            $karyawanId = session('user_karyawan');
            
            // Update karyawan data
            DB::table('karyawan')
                ->where('id_karyawan', $karyawanId)
                ->update([
                    'nama_lengkap' => $request->nama_lengkap,
                    'no_hp' => $request->no_hp,
                    'alamat' => $request->alamat,
                    'jabatan' => $request->jabatan,
                    'updated_at' => now(),
                ]);

            // Update email di tabel akun
            DB::table('akun')
                ->where('id_akun', $userId)
                ->update([
                    'email' => $request->email,
                    'updated_at' => now(),
                ]);

            // Update session data
            session([
                'nama_lengkap' => $request->nama_lengkap,
                'email' => $request->email,
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
                'jabatan' => $request->jabatan,
                'updated_at' => now()
            ]);
            
            // Save session to ensure it persists
            session()->save();

            return redirect()->route('kadivmekanik.profile', ['from' => 'crud'])->with('success', 'Data berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui profile: ' . $e->getMessage());
        }
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        try {
            $user = DB::table('akun')->where('id_akun', session('user_id'))->first();
            
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->with('error', 'Password lama tidak sesuai!');
            }

            DB::table('akun')
                ->where('id_akun', session('user_id'))
                ->update([
                    'password' => Hash::make($request->new_password),
                    'updated_at' => now(),
                ]);

            return redirect()->route('kadivmekanik.profile', ['from' => 'crud'])->with('success', 'Password berhasil diubah!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengubah password: ' . $e->getMessage());
        }
    }

    /**
     * Upload photo.
     */
    public function uploadPhoto(Request $request)
    {
        return redirect()->route('kadivmekanik.profile')->with('error', 'Fitur upload foto dinonaktifkan.');
    }

    /**
     * Delete photo.
     */
    public function deletePhoto()
    {
        return redirect()->route('kadivmekanik.profile')->with('error', 'Fitur foto profil dinonaktifkan.');
    }

    /**
     * Display notifications page.
     */
    public function notifications()
    {
        $notifications = DB::table('notifications')
            ->where('user_id', session('user_id'))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('kadivmekanik.notifications', compact('notifications'));
    }

    /**
     * Get unread notifications.
     */
    public function unreadNotifications()
    {
        $unreadCount = DB::table('notifications')
            ->where('user_id', session('user_id'))
            ->where('read_at', null)
            ->count();
        
        return response()->json(['count' => $unreadCount]);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead($id)
    {
        DB::table('notifications')
            ->where('id', $id)
            ->where('user_id', session('user_id'))
            ->update(['read_at' => now()]);
        
        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        DB::table('notifications')
            ->where('user_id', session('user_id'))
            ->where('read_at', null)
            ->update(['read_at' => now()]);
        
        return response()->json(['success' => true]);
    }

    /**
     * Display settings page.
     */
    public function settings()
    {
        return view('kadivmekanik.settings');
    }

    /**
     * Update settings.
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'email_notifications' => 'boolean',
            'theme' => 'in:light,dark',
        ]);

        try {
            // Update user settings in session or database
            session([
                'email_notifications' => $request->email_notifications ?? false,
                'theme' => $request->theme ?? 'light',
            ]);

            return redirect()->route('kadivmekanik.settings', ['from' => 'crud'])->with('success', 'Data berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui pengaturan: ' . $e->getMessage());
        }
    }

    /**
     * Display activity log.
     */
    public function activityLog()
    {
        $activities = DB::table('activity_logs')
            ->where('user_id', session('user_id'))
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('kadivmekanik.activity_log', compact('activities'));
    }

    /**
     * Get work order status.
     */
    public function getWorkOrderStatus($id)
    {
        $workOrder = SuratPengajuan::find($id);
        
        if (!$workOrder) {
            return response()->json(['error' => 'Work order not found'], 404);
        }

        return response()->json([
            'id' => $workOrder->id_surat_pengajuan,
            'status' => $workOrder->status,
            'updated_at' => $workOrder->updated_at,
        ]);
    }

    /**
     * Get laporan count.
     */
    public function getLaporanCount()
    {
        $counts = [
            'harian_mekanik' => LaporanHarianMekanik::count(),
            'pemakaian_barang' => LaporanPemakaianBarang::count(),
            'work_order' => SuratPengajuan::count(),
        ];

        return response()->json($counts);
    }

    /**
     * Get dashboard stats.
     */
    public function getDashboardStats()
    {
        $stats = [
            'total_work_order' => SuratPengajuan::count(),
            'pending_work_order' => SuratPengajuan::where('status', 'Menunggu')->count(),
            'total_laporan_harian' => LaporanHarianMekanik::count(),
            'total_laporan_barang' => LaporanPemakaianBarang::count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get recent activities.
     */
    public function getRecentActivities()
    {
        $activities = SuratPengajuan::join('karyawan', 'surat_pengajuan.id_akun', '=', 'karyawan.id_karyawan')
            ->select('surat_pengajuan.*', 'karyawan.nama_lengkap')
            ->orderBy('surat_pengajuan.created_at', 'desc')
            ->limit(5)
            ->get();
        
        return response()->json($activities);
    }

    /**
     * Generate work order number.
     */
    private function generateWorkOrderNumber()
    {
        $year = Carbon::now('Asia/Makassar')->year;
        $month = Carbon::now('Asia/Makassar')->month;
        
        // Get the last work order number for this year
        $lastWorkOrder = SuratPengajuan::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($lastWorkOrder) {
            // Extract sequence number from last work order
            $lastNumber = explode('/', $lastWorkOrder->no_surat_pengajuan)[0];
            $sequence = str_pad((int)$lastNumber + 1, 2, '0', STR_PAD_LEFT);
        } else {
            $sequence = '01';
        }
        
        return "{$sequence}/MKN/KCE/{$year}";
    }
}