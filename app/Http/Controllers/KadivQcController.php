<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\SuratPengajuan;
use App\Models\Divisi;
use App\Models\Unit;
use App\Models\DaftarBarang;
use App\Models\PermintaanBarang;
use App\Models\DetailBarangPermintaan;
use Carbon\Carbon;

class KadivQcController extends Controller
{
    /**
     * Display the dashboard page.
     */
    public function dashboard()
    {
        $userId = Session::get('user_id');
        $userDivisi = Session::get('user_divisi');
        
        // Statistik - WO yang dibuat oleh divisi Produksi
        $totalWODibuat = SuratPengajuan::where('id_akun', $userId)
            ->where('id_divisi', $userDivisi)
            ->count();
        
        // Statistik - WO yang diterima oleh divisi Quality Control
        $totalWODiterima = SuratPengajuan::where('ditujukan', 'LIKE', '%Quality Control%')
            ->where('id_divisi', '!=', $userDivisi)
            ->count();
        
        $woPending = SuratPengajuan::where('id_akun', $userId)
            ->where('id_divisi', $userDivisi)
            ->where('status', 'Menunggu')
            ->count();
        
        $woSelesai = SuratPengajuan::where('id_akun', $userId)
            ->where('id_divisi', $userDivisi)
            ->where('status', 'Selesai')
            ->count();
        
        // Chart data
        $monthlyWOTrend = SuratPengajuan::select(
                DB::raw('MONTH(tanggal) as month'),
                DB::raw('YEAR(tanggal) as year'),
                DB::raw('count(*) as total')
            )
            ->where('id_akun', $userId)
            ->where('id_divisi', $userDivisi)
            ->where('tanggal', '>=', DB::raw('DATE_SUB(NOW(), INTERVAL 6 MONTH)'))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();
        
        $woStatus = SuratPengajuan::select('status', DB::raw('count(*) as total'))
            ->where('id_akun', $userId)
            ->where('id_divisi', $userDivisi)
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status')
            ->toArray();
        
        // Recent activities
        $recentActivities = SuratPengajuan::where('id_akun', $userId)
            ->where('id_divisi', $userDivisi)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('kadivqc.dashboard', compact(
            'totalWODibuat',
            'totalWODiterima',
            'woPending',
            'woSelesai',
            'monthlyWOTrend',
            'woStatus',
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
        $userDivisiNama = DB::table('divisi')->where('id_divisi', $userDivisi)->value('nama_divisi') ?? 'Quality Control';
        
        // WO yang dibuat oleh divisi pengaju (akun yang login)
        $workOrders = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder'])
            ->where('divisi_pengaju', $userDivisiNama)
            ->orderBy('created_at', 'desc')
            ->get();
        
        $divisi = Divisi::where('nama_divisi', '!=', 'Administrator')->get();
        $unit = Unit::all();
        $karyawan = DB::table('karyawan')->get();
        $unitOptions = Unit::all();
        $jenisWorkOrder = \App\Models\JenisWorkOrder::all();
        $daftarBarang = \App\Models\DaftarBarang::all();
        
        $nextWorkOrderNumber = $this->generateWorkOrderNumber();
        
        return view('kadivqc.work_order', compact('workOrders', 'divisi', 'unit', 'nextWorkOrderNumber', 'karyawan', 'unitOptions', 'jenisWorkOrder', 'daftarBarang'));
    }
    
    /**
     * Display daftar work order page (WO yang diterima dari divisi lain).
     */
    public function daftarWorkOrder()
    {
        $userDivisi = Session::get('user_divisi');
        
        // Ambil nama divisi user yang login
        $userDivisiNama = DB::table('divisi')->where('id_divisi', $userDivisi)->value('nama_divisi') ?? 'Quality Control';
        
        // WO yang diterima oleh divisi user yang login (dari divisi lain)
        // Hanya menampilkan yang status Menunggu (id_verifikator = 1)
        $workOrders = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder'])
            ->where('ditujukan', $userDivisiNama)
            ->where('divisi_pengaju', '!=', $userDivisiNama)
            ->where('id_verifikator', 1) // Hanya status Menunggu (1)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('kadivqc.daftar_work_order', compact('workOrders'));
    }
    
    /**
     * Display riwayat work order page (WO yang sudah selesai).
     */
    public function riwayatWorkOrder()
    {
        $userDivisi = Session::get('user_divisi');
        
        // Ambil nama divisi user yang login
        $userDivisiNama = DB::table('divisi')->where('id_divisi', $userDivisi)->value('nama_divisi') ?? 'Quality Control';
        
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
        
        return view('kadivqc.riwayat_work_order', compact('workOrders'));
    }
    
    /**
     * Generate work order number.
     */
    private function generateWorkOrderNumber()
    {
        $year = Carbon::now('Asia/Makassar')->year;
        $month = Carbon::now('Asia/Makassar')->month;
        
        $lastWorkOrder = SuratPengajuan::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($lastWorkOrder) {
            $lastNumber = explode('/', $lastWorkOrder->no_surat_pengajuan)[0];
            $sequence = str_pad((int)$lastNumber + 1, 2, '0', STR_PAD_LEFT);
        } else {
            $sequence = '01';
        }
        
        return "{$sequence}/QC/KCE/{$year}";
    }
    
    /**
     * Generate nomor permintaan barang.
     */
    private function generateNoPermintaan()
    {
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;
        
        // Lock table untuk menghindari race condition
        $lastPermintaan = PermintaanBarang::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->lockForUpdate()
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($lastPermintaan) {
            $lastNumber = explode('/', $lastPermintaan->no_permintaan_barang)[0];
            $sequence = str_pad((int)$lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $sequence = '001';
        }
        
        // Loop untuk memastikan nomor benar-benar unik
        $maxAttempts = 50;
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            $noPermintaan = "{$sequence}/LOG/KCE/{$year}";
            
            // Cek apakah nomor sudah ada dengan lock
            $existing = PermintaanBarang::where('no_permintaan_barang', $noPermintaan)
                ->lockForUpdate()
                ->first();
            
            if (!$existing) {
                return $noPermintaan;
            }
            
            // Jika sudah ada, increment sequence
            $lastNumber = explode('/', $existing->no_permintaan_barang)[0];
            $sequence = str_pad((int)$lastNumber + 1, 3, '0', STR_PAD_LEFT);
            $attempt++;
        }
        
        // Jika masih gagal setelah banyak percobaan, gunakan timestamp untuk memastikan unik
        $timestamp = time();
        $sequence = str_pad((int)$sequence, 3, '0', STR_PAD_LEFT);
        return "{$sequence}/LOG/KCE/{$year}-" . substr($timestamp, -4);
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
            $dokumentasiPath = null;
            if ($request->hasFile('dokumentasi')) {
                $dokumentasiPath = $request->file('dokumentasi')->store('work-orders', 'public');
            }

            $unitId = 1;
            $selectedUnit = Unit::where('nama_unit', $request->unit)->first();
            if ($selectedUnit) {
                $unitId = $selectedUnit->id_unit;
            }

            $workOrderNumber = $this->generateWorkOrderNumber();

            $workOrder = SuratPengajuan::create([
                'no_surat_pengajuan' => $workOrderNumber,
                'ditujukan' => $request->ditujukan,
                'id_jenis_wo' => $request->id_jenis_wo,
                'tanggal' => $request->tanggal,
                'divisi_pengaju' => $request->divisi_pengaju,
                'unit' => $request->unit,
                'uraian' => $request->uraian,
                'dokumentasi' => $dokumentasiPath,
                'id_divisi' => Session::get('user_divisi', 1),
                'id_peran' => 2,
                'id_verifikator' => 1,
                'id_akun' => Session::get('user_id', 1),
                'id_unit' => $unitId
            ]);

            // Jika ada barang yang diisi, buat PermintaanBarang otomatis
            if ($request->has('barang') && is_array($request->barang) && count($request->barang) > 0) {
                $barangItems = array_filter($request->barang, function($item) {
                    return !empty($item['nama_barang']) && !empty($item['jumlah']);
                });
                
                if (count($barangItems) > 0) {
                    // Hitung total estimasi harga
                    $totalHarga = 0;
                    foreach ($barangItems as $item) {
                        $totalHarga += ($item['estimasi_harga'] ?? 0) * ($item['jumlah'] ?? 0);
                    }
                    
                    // Generate nomor permintaan
                    $noPermintaan = $this->generateNoPermintaan();
                    
                    // Buat PermintaanBarang
                    $permintaan = PermintaanBarang::create([
                        'no_permintaan_barang' => $noPermintaan,
                        'id_surat_pengajuan' => $workOrder->id_surat_pengajuan,
                        'tanggal_permintaan' => $request->tanggal,
                        'status' => 'Menunggu Logistik',
                        'total_estimasi_harga' => $totalHarga,
                        'id_akun' => Session::get('user_id', 1),
                    ]);
                    
                    // Simpan detail barang
                    foreach ($barangItems as $item) {
                        // Cari atau buat master stok barang
                        $master = DaftarBarang::firstOrCreate(
                            [
                                'nama_barang' => $item['nama_barang'],
                                'satuan' => $item['satuan'] ?? null,
                            ],
                            [
                                'stok' => 0,
                            ]
                        );
                        
                        DetailBarangPermintaan::create([
                            'id_permintaan_barang' => $permintaan->id_permintaan_barang,
                            'id_daftar_barang_master' => $master->id_daftar_barang,
                            'nama_barang' => $item['nama_barang'],
                            'jumlah' => (int)($item['jumlah'] ?? 0),
                            'satuan' => $item['satuan'] ?? null,
                            'estimasi_harga' => isset($item['estimasi_harga']) ? (float)$item['estimasi_harga'] : null,
                        ]);
                    }
                }
            }

            return redirect()->route('kadivqc.work-order', ['from' => 'crud'])->with('success', 'Data berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->route('kadivqc.work-order')->with('error', 'Gagal membuat work order: ' . $e->getMessage());
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
        if ($workOrder->dokumentasi && $workOrder->dokumentasi !== '-' && !empty(trim($workOrder->dokumentasi))) {
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

            // Handle file upload if new file is provided or delete if requested
            $dokumentasiPath = $workOrder->dokumentasi;
            
            // Check if user wants to delete dokumentasi
            if ($request->has('delete_dokumentasi') && $request->delete_dokumentasi == '1') {
                // Delete old file if exists
                if ($workOrder->dokumentasi && Storage::disk('public')->exists($workOrder->dokumentasi)) {
                    Storage::disk('public')->delete($workOrder->dokumentasi);
                }
                $dokumentasiPath = null;
            } elseif ($request->hasFile('dokumentasi')) {
                // Delete old file if exists
                if ($workOrder->dokumentasi && Storage::disk('public')->exists($workOrder->dokumentasi)) {
                    Storage::disk('public')->delete($workOrder->dokumentasi);
                }
                $dokumentasiPath = $request->file('dokumentasi')->store('work-orders', 'public');
            }

            $unitId = 1;
            $selectedUnit = Unit::where('nama_unit', $request->unit)->first();
            if ($selectedUnit) {
                $unitId = $selectedUnit->id_unit;
            }

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

            return redirect()->route('kadivqc.work-order', ['from' => 'crud'])->with('success', 'Data berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->route('kadivqc.work-order')->with('error', 'Gagal mengupdate work order: ' . $e->getMessage());
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

            if ($workOrder->dokumentasi && Storage::disk('public')->exists($workOrder->dokumentasi)) {
                Storage::disk('public')->delete($workOrder->dokumentasi);
            }

            $workOrder->delete();

            // Simpan success message di session sebelum return JSON
            session()->flash('success', 'Data berhasil dihapus!');
            session()->flash('from_crud', true);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus!',
                'redirect' => route('kadivqc.work-order')
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
                    'redirect' => route('kadivqc.daftar-work-order', ['from' => 'crud'])
                ], 403);
            }
            
            $workOrder->update([
                'id_verifikator' => 2, // 2 = Disetujui
                'status' => 'Disetujui'
            ]);
            
            // Set session message untuk notifikasi toast
            Session::flash('success', 'Work Order berhasil disetujui!');
            
            // Return JSON untuk AJAX dengan redirect URL yang sudah include from=crud
            $redirectUrl = route('kadivqc.daftar-work-order', ['from' => 'crud']);
            
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
                'redirect' => route('kadivqc.daftar-work-order', ['from' => 'crud'])
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
                    'redirect' => route('kadivqc.daftar-work-order', ['from' => 'crud'])
                ], 403);
            }
            
            $workOrder->update([
                'id_verifikator' => 3, // 3 = Ditolak
                'status' => 'Ditolak'
            ]);
            
            // Set session message untuk notifikasi toast
            Session::flash('success', 'Work Order berhasil ditolak!');
            
            // Return JSON untuk AJAX dengan redirect URL yang sudah include from=crud
            $redirectUrl = route('kadivqc.daftar-work-order', ['from' => 'crud']);
            
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
                'redirect' => route('kadivqc.daftar-work-order', ['from' => 'crud'])
            ], 500);
        }
    }
    
    /**
     * Display profile page.
     */
    public function profile()
    {
        $userId = Session::get('user_id');
        
        $user = DB::table('akun')
            ->join('karyawan', 'akun.id_karyawan', '=', 'karyawan.id_karyawan')
            ->join('divisi', 'akun.id_divisi', '=', 'divisi.id_divisi')
            ->where('akun.id_akun', $userId)
            ->select('akun.*', 'karyawan.*', 'divisi.nama_divisi')
            ->first();
        
        return view('kadivqc.profile', compact('user'));
    }
    
    /**
     * Update profile.
     */
    public function updateProfile(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'alamat' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
        ]);
        
        try {
            $userId = Session::get('user_id');
            $karyawanId = Session::get('user_karyawan');
            
            DB::table('karyawan')
                ->where('id_karyawan', $karyawanId)
                ->update([
                    'nama_lengkap' => $request->nama_lengkap,
                    'no_hp' => $request->no_hp,
                    'alamat' => $request->alamat,
                    'jabatan' => $request->jabatan,
                    'updated_at' => now(),
                ]);
            
            session([
                'nama_lengkap' => $request->nama_lengkap,
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
                'jabatan' => $request->jabatan,
                'updated_at' => now()
            ]);
            session()->save();
            
            return redirect()->route('kadivqc.profile', ['from' => 'crud'])->with('success', 'Data berhasil diperbarui');
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
            $user = DB::table('akun')->where('id_akun', Session::get('user_id'))->first();
            
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->with('error', 'Password lama tidak sesuai!');
            }
            
            DB::table('akun')
                ->where('id_akun', Session::get('user_id'))
                ->update([
                    'password' => Hash::make($request->new_password),
                    'updated_at' => now(),
                ]);
            
            return redirect()->route('kadivqc.profile', ['from' => 'crud'])->with('success', 'Password berhasil diubah!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengubah password: ' . $e->getMessage());
        }
    }
}
