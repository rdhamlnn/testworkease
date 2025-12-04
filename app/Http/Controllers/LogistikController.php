<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\SuratPengajuan;
use App\Models\PermintaanBarang;
use App\Models\StatusWo;
use App\Models\DaftarBarang; // master stok
use App\Models\DetailBarangPermintaan; // pivot detail permintaan
use App\Models\Divisi;
use App\Models\Unit;
use App\Models\JenisWorkOrder;
use Carbon\Carbon;

class LogistikController extends Controller
{
    /**
     * Display the dashboard page.
     */
    public function dashboard()
    {
        $userId = Session::get('user_id');
        
        // Statistik
        $totalWODicek = SuratPengajuan::count();
        $totalPermintaan = PermintaanBarang::count();
        $barangMasuk = PermintaanBarang::where('status', 'Diterima Logistik')->count();
        $barangKeluar = PermintaanBarang::where('status', 'Diserahkan ke Divisi')->count();
        
        // Chart data
        $monthlyWOTrend = SuratPengajuan::select(
                DB::raw('MONTH(tanggal) as month'),
                DB::raw('YEAR(tanggal) as year'),
                DB::raw('count(*) as total')
            )
            ->where('tanggal', '>=', DB::raw('DATE_SUB(NOW(), INTERVAL 6 MONTH)'))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();
        
        $statusPermintaan = PermintaanBarang::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status')
            ->toArray();
        
        // Recent activities
        $recentActivities = SuratPengajuan::join('akun', 'surat_pengajuan.id_akun', '=', 'akun.id_akun')
            ->join('karyawan', 'akun.id_karyawan', '=', 'karyawan.id_karyawan')
            ->select('surat_pengajuan.*', 'karyawan.nama_lengkap')
            ->orderBy('surat_pengajuan.created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('logistik.dashboard', compact(
            'totalWODicek',
            'totalPermintaan',
            'barangMasuk',
            'barangKeluar',
            'monthlyWOTrend',
            'statusPermintaan',
            'recentActivities'
        ));
    }
    
    /**
     * Display work order masuk page.
     */
    public function workOrder()
    {
        $divisiId = Session::get('user_divisi');
        $divisiPengaju = Divisi::where('id_divisi', $divisiId)->value('nama_divisi') ?? 'Logistik';
        $divisi = Divisi::where('nama_divisi', '!=', 'Administrator')->get();
        $divisiTujuan = Divisi::where('id_divisi', '!=', $divisiId)->orderBy('nama_divisi')->get();
        $unit = Unit::orderBy('nama_unit')->get();
        $jenisWorkOrder = JenisWorkOrder::all();
        $daftarBarang = DaftarBarang::all();
        $nextNoWO = $this->generateWorkOrderNumber('LOG');
        $workOrders = SuratPengajuan::with(['unit', 'jenisWorkOrder', 'verifikator'])
            ->where('divisi_pengaju', $divisiPengaju)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('logistik.work_order', compact('divisiPengaju', 'divisi', 'divisiTujuan', 'unit', 'jenisWorkOrder', 'daftarBarang', 'nextNoWO', 'workOrders'));
    }

    /**
     * Display daftar pengajuan work order page (WO yang diterima dari divisi lain).
     */
    public function daftarWorkOrder()
    {
        $userDivisi = Session::get('user_divisi');
        $userDivisiNama = DB::table('divisi')->where('id_divisi', $userDivisi)->value('nama_divisi') ?? 'Logistik';
        
        // WO yang diterima oleh divisi user yang login (dari divisi lain)
        $workOrders = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder'])
            ->where('ditujukan', $userDivisiNama)
            ->where('divisi_pengaju', '!=', $userDivisiNama)
            ->where('id_verifikator', 1) // Hanya status Menunggu
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('logistik.daftar_work_order', compact('workOrders'));
    }

    public function riwayatWorkOrder()
    {
        $userDivisi = Session::get('user_divisi');
        $userDivisiNama = DB::table('divisi')->where('id_divisi', $userDivisi)->value('nama_divisi') ?? 'Logistik';
        
        // WO yang dibuat dan diterima oleh divisi user yang login
        $workOrdersDibuat = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder'])
            ->where('divisi_pengaju', $userDivisiNama)
            ->whereIn('id_verifikator', [2, 3]) // Disetujui atau Ditolak
            ->get();
        
        $workOrdersDiterima = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder'])
            ->where('ditujukan', $userDivisiNama)
            ->where('divisi_pengaju', '!=', $userDivisiNama)
            ->whereIn('id_verifikator', [2, 3]) // Disetujui atau Ditolak
            ->get();
        
        $workOrders = $workOrdersDibuat->merge($workOrdersDiterima)->sortByDesc('created_at');
        
        return view('logistik.riwayat_work_order', compact('workOrders'));
    }

    /**
     * Store new work order submission.
     */
    public function storeWorkOrder(Request $request)
    {
        $request->validate([
            'no_surat_pengajuan' => 'required|string|max:255',
            'ditujukan' => 'required|string|max:255',
            'id_jenis_wo' => 'required|exists:jenis_work_order,id_jenis_wo',
            'tanggal' => 'required|date',
            'unit' => 'required|string|max:255',
            'uraian' => 'required|string',
            'dokumentasi' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        try {
            // Get unit ID
            $unitId = 1; // Default
            $selectedUnit = Unit::where('nama_unit', $request->unit)->first();
            if ($selectedUnit) {
                $unitId = $selectedUnit->id_unit;
            }

            $divisiId = Session::get('user_divisi');
            $divisiNama = DB::table('divisi')->where('id_divisi', $divisiId)->value('nama_divisi') ?? 'Logistik';

            $dokumentasiPath = null;
            if ($request->hasFile('dokumentasi')) {
                $dokumentasiPath = $request->file('dokumentasi')->store('work-orders', 'public');
            }

            SuratPengajuan::create([
                'no_surat_pengajuan' => $request->no_surat_pengajuan,
                'ditujukan' => $request->ditujukan,
                'id_jenis_wo' => $request->id_jenis_wo,
                'tanggal' => $request->tanggal,
                'divisi_pengaju' => $divisiNama,
                'unit' => $request->unit,
                'uraian' => $request->uraian,
                'dokumentasi' => $dokumentasiPath,
                'status' => 'Menunggu',
                'id_divisi' => $divisiId,
                'id_peran' => Session::get('user_peran'),
                'id_verifikator' => 1,
                'id_akun' => Session::get('user_id'),
                'id_unit' => $unitId,
            ]);

            return redirect()->route('logistik.work-order', ['from' => 'crud'])->with('success', 'Data berhasil ditambahkan');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat work order: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Search units for autocomplete.
     */
    public function searchUnits(Request $request)
    {
        $searchTerm = $request->get('term', '');
        
        $units = Unit::where('nama_unit', 'like', "%{$searchTerm}%")
            ->orWhere('kode_unit', 'like', "%{$searchTerm}%")
            ->limit(10)
            ->get()
            ->map(function ($unit) {
                return [
                    'id' => $unit->id_unit,
                    'label' => $unit->nama_unit,
                    'value' => $unit->nama_unit,
                    'kode' => $unit->kode_unit
                ];
            });

        return response()->json($units);
    }

    /**
     * Update work order milik logistik.
     */
    public function updateWorkOrder(Request $request, $id)
    {
        $request->validate([
            'ditujukan' => 'required|string|max:255',
            'id_jenis_wo' => 'required|exists:jenis_work_order,id_jenis_wo',
            'tanggal' => 'required|date',
            'unit' => 'required|string|max:255',
            'uraian' => 'required|string',
            'dokumentasi' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $divisiId = Session::get('user_divisi');
        $divisiNama = DB::table('divisi')->where('id_divisi', $divisiId)->value('nama_divisi') ?? 'Logistik';

        $workOrder = SuratPengajuan::findOrFail($id);
        if ($workOrder->divisi_pengaju !== $divisiNama) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah data ini.');
        }

        // Get unit ID
        $unitId = 1; // Default
        $selectedUnit = Unit::where('nama_unit', $request->unit)->first();
        if ($selectedUnit) {
            $unitId = $selectedUnit->id_unit;
        }
        
        // Validasi: tidak bisa edit jika sudah disetujui atau ditolak
        if (in_array($workOrder->id_verifikator, [2, 3])) {
            $statusText = $workOrder->id_verifikator == 2 ? 'disetujui' : 'ditolak';
            Session::flash('error', "Work Order tidak dapat diedit karena sudah {$statusText}.");
            return redirect()->route('logistik.work-order', ['from' => 'crud'])
                ->with('error', "Work Order tidak dapat diedit karena sudah {$statusText}.");
        }

        try {
            $dokumentasiPath = $workOrder->dokumentasi;

            if ($request->hasFile('dokumentasi')) {
                if ($dokumentasiPath && Storage::disk('public')->exists($dokumentasiPath)) {
                    Storage::disk('public')->delete($dokumentasiPath);
                }
                $dokumentasiPath = $request->file('dokumentasi')->store('work-orders', 'public');
            }

            $workOrder->update([
                'ditujukan' => $request->ditujukan,
                'id_jenis_wo' => $request->id_jenis_wo,
                'tanggal' => $request->tanggal,
                'unit' => $request->unit,
                'uraian' => $request->uraian,
                'dokumentasi' => $dokumentasiPath,
                'id_unit' => $unitId,
            ]);

            return redirect()->route('logistik.work-order', ['from' => 'crud'])->with('success', 'Work Order berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Work Order: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Hapus work order milik logistik.
     */
    public function destroyWorkOrder($id)
    {
        $divisiId = Session::get('user_divisi');
        $divisiNama = Divisi::where('id_divisi', $divisiId)->value('nama_divisi') ?? 'Logistik';

        try {
            $workOrder = SuratPengajuan::findOrFail($id);
            if ($workOrder->divisi_pengaju !== $divisiNama) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menghapus data ini.'
                ], 403);
            }
            
            // Validasi: tidak bisa hapus jika sudah disetujui atau ditolak
            if (in_array($workOrder->id_verifikator, [2, 3])) {
                $statusText = $workOrder->id_verifikator == 2 ? 'disetujui' : 'ditolak';
                Session::flash('error', "Work Order tidak dapat dihapus karena sudah {$statusText}.");
                return response()->json([
                    'success' => false,
                    'message' => "Work Order tidak dapat dihapus karena sudah {$statusText}.",
                    'redirect' => route('logistik.work-order', ['from' => 'crud'])
                ], 403);
            }

            if ($workOrder->dokumentasi && Storage::disk('public')->exists($workOrder->dokumentasi)) {
                Storage::disk('public')->delete($workOrder->dokumentasi);
            }

            $workOrder->delete();

            // Simpan success message di session sebelum return JSON
            session()->flash('success', 'Data berhasil dihapus');
            session()->flash('from_crud', true);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus',
                'redirect' => route('logistik.work-order')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display permintaan barang page.
     */
    public function permintaanBarang()
    {
        $userId = Session::get('user_id');
        
        $permintaanBarang = PermintaanBarang::with(['suratPengajuan', 'akun', 'daftarBarang'])
            ->where('id_logistik', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('logistik.permintaan_barang', compact('permintaanBarang'));
    }

    /**
     * Display daftar barang (master stok) page.
     */
    public function daftarBarang()
    {
        $daftarBarang = DaftarBarang::orderBy('nama_barang', 'asc')->get();

        return view('logistik.daftar_barang', compact('daftarBarang'));
    }

    /**
     * Get daftar barang by ID (for edit).
     */
    public function getDaftarBarang($id)
    {
        try {
            $barang = DaftarBarang::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $barang
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Store new daftar barang.
     */
    public function storeDaftarBarang(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'satuan' => 'nullable|string|max:50',
            'stok' => 'required|integer|min:0',
            'harga_barang' => 'nullable|numeric|min:0',
            'path_foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            $data = [
                'nama_barang' => $request->nama_barang,
                'satuan' => $request->satuan,
                'stok' => $request->stok,
                'harga_barang' => $request->harga_barang ? (float) $request->harga_barang : null,
            ];

            // Handle upload foto
            if ($request->hasFile('path_foto')) {
                $fotoPath = $request->file('path_foto')->store('barang', 'public');
                $data['path_foto'] = $fotoPath;
            }

            DaftarBarang::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil ditambahkan!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan barang: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update daftar barang.
     */
    public function updateDaftarBarang(Request $request, $id)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'satuan' => 'nullable|string|max:50',
            'stok' => 'required|integer|min:0',
            'harga_barang' => 'nullable|numeric|min:0',
            'path_foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            $barang = DaftarBarang::findOrFail($id);

            $data = [
                'nama_barang' => $request->nama_barang,
                'satuan' => $request->satuan,
                'stok' => $request->stok,
                'harga_barang' => $request->harga_barang ? (float) $request->harga_barang : null,
            ];

            // Handle upload foto baru
            if ($request->hasFile('path_foto')) {
                // Hapus foto lama jika ada
                if ($barang->path_foto && Storage::disk('public')->exists($barang->path_foto)) {
                    Storage::disk('public')->delete($barang->path_foto);
                }
                $fotoPath = $request->file('path_foto')->store('barang', 'public');
                $data['path_foto'] = $fotoPath;
            }

            $barang->update($data);

            // Simpan success message di session untuk toast notification
            Session::flash('success', 'Barang berhasil diperbarui!');
            Session::flash('from_crud', true);

            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil diperbarui!',
                'redirect' => route('logistik.daftar-barang', ['from' => 'crud'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui barang: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete daftar barang.
     */
    public function destroyDaftarBarang($id)
    {
        try {
            $barang = DaftarBarang::findOrFail($id);

            // Hapus foto jika ada
            if ($barang->path_foto && Storage::disk('public')->exists($barang->path_foto)) {
                Storage::disk('public')->delete($barang->path_foto);
            }

            $barang->delete();

            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil dihapus!',
                'redirect' => route('logistik.daftar-barang')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus barang: ' . $e->getMessage(),
                'redirect' => route('logistik.daftar-barang')
            ], 500);
        }
    }
    
    /**
     * Display terima barang page.
     */
    public function terimaBarang()
    {
        $statusDikirimId = StatusWo::where('nama_status', 'Dikirim Purchasing')->value('id_status_wo');

        $permintaanBarang = PermintaanBarang::with(['suratPengajuan', 'akun', 'statusWo', 'daftarBarang'])
            ->where('id_status_wo', $statusDikirimId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('logistik.terima_barang', compact('permintaanBarang'));
    }
    
    /**
     * Display serahkan barang page.
     */
    public function serahkanBarang()
    {
        $statusDiterimaId = StatusWo::where('nama_status', 'Diterima Logistik')->value('id_status_wo');

        $permintaanBarang = PermintaanBarang::with(['suratPengajuan', 'akun', 'statusWo', 'daftarBarang'])
            ->where('id_status_wo', $statusDiterimaId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('logistik.serahkan_barang', compact('permintaanBarang'));
    }
    
    /**
     * Create permintaan barang page.
     */
    public function createPermintaanBarang($id)
    {
        $workOrder = SuratPengajuan::findOrFail($id);
        return view('logistik.create_permintaan_barang', compact('workOrder'));
    }
    
    /**
     * Store permintaan barang.
     */
    public function storePermintaanBarang(Request $request)
    {
        $request->validate([
            'id_surat_pengajuan' => 'required|exists:surat_pengajuan,id_surat_pengajuan',
            'tanggal_permintaan' => 'required|date',
            'daftar_barang' => 'required|string',
            'total_estimasi_harga' => 'required',
            'catatan_logistik' => 'nullable|string',
        ]);
        
        try {
            // Parse total_estimasi_harga jika ada format currency
            $totalHarga = $request->total_estimasi_harga;
            if (is_string($totalHarga)) {
                $totalHarga = floatval(str_replace(['Rp ', '.', ','], '', $totalHarga));
            }
            
            // Validate daftar_barang is valid JSON dan siapkan data detail
            $daftarBarangJson = $request->daftar_barang;
            $decoded = [];
            if (is_string($daftarBarangJson)) {
                $decoded = json_decode($daftarBarangJson, true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                    return back()->with('error', 'Format daftar barang tidak valid!')->withInput();
                }
            }
            
            $noPermintaan = $this->generateNoPermintaan();

            // Simpan header permintaan barang
            $permintaan = PermintaanBarang::create([
                'no_permintaan_barang' => $noPermintaan,
                'id_surat_pengajuan' => $request->id_surat_pengajuan,
                'tanggal_permintaan' => $request->tanggal_permintaan,
                'status' => 'Menunggu Purchasing',
                'total_estimasi_harga' => $totalHarga,
                'catatan_logistik' => $request->catatan_logistik,
                'id_logistik' => Session::get('user_id'),
                'id_akun' => Session::get('user_id'),
            ]);

            // Simpan detail barang ke tabel pivot detail_barang_permintaan
            // sekaligus mapping ke master stok (daftar_barang)
            if (is_array($decoded)) {
                foreach ($decoded as $item) {
                    if (!isset($item['nama_barang']) || $item['nama_barang'] === '') {
                        continue;
                    }

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
                        'jumlah' => isset($item['jumlah']) ? (int) $item['jumlah'] : 0,
                        'satuan' => $item['satuan'] ?? null,
                        'estimasi_harga' => isset($item['estimasi_harga']) ? (float) $item['estimasi_harga'] : null,
                    ]);
                }
            }
            
            return redirect()->route('logistik.permintaan-barang', ['from' => 'crud'])->with('success', 'Data berhasil ditambahkan');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat permintaan barang: ' . $e->getMessage());
        }
    }
    
    /**
     * Proses serahkan barang langsung (approve + buat permintaan + redirect).
     */
    public function prosesSerahkanBarangLangsung(Request $request, $id)
    {
        try {
            $workOrder = SuratPengajuan::with('jenisWorkOrder')->findOrFail($id);
            $userDivisi = Session::get('user_divisi');
            $userDivisiNama = DB::table('divisi')->where('id_divisi', $userDivisi)->value('nama_divisi');
            
            // Validasi akses
            if ($workOrder->ditujukan !== $userDivisiNama) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk memproses work order ini.'
                ], 403);
            }
            
            // 1. Approve work order
            $workOrder->update([
                'id_verifikator' => 2, // 2 = Disetujui
                'status' => 'Disetujui'
            ]);
            
            // 2. Cek apakah permintaan barang sudah ada
            $permintaanBarang = PermintaanBarang::where('id_surat_pengajuan', $id)->first();
            
            // 3. Parse daftar barang dari request atau work order
            $daftarBarangArray = [];
            
            // Ambil dari request jika ada (dari hasil cek stock)
            if ($request->has('hasil_cek') && $request->hasil_cek) {
                $hasilCek = json_decode($request->hasil_cek, true);
                if (is_array($hasilCek)) {
                    foreach ($hasilCek as $item) {
                        if (isset($item['nama_barang'])) {
                            $daftarBarangArray[] = [
                                'nama_barang' => $item['nama_barang'],
                                'jumlah' => $item['qty_dibutuhkan'] ?? 1,
                                'satuan' => $item['satuan'] ?? '-'
                            ];
                        }
                    }
                }
            }
            
            // Jika belum ada dari request, ambil dari work order atau permintaan barang yang sudah ada
            if (empty($daftarBarangArray)) {
                if ($permintaanBarang && $permintaanBarang->daftarBarang) {
                    // Jika sudah ada permintaan barang, ambil dari detail barang
                    $permintaanBarang->load('daftarBarang');
                    foreach ($permintaanBarang->daftarBarang as $detail) {
                        $daftarBarangArray[] = [
                            'nama_barang' => $detail->nama_barang,
                            'jumlah' => $detail->jumlah,
                            'satuan' => $detail->satuan ?? '-'
                        ];
                    }
                } else {
                    // Cek jenis work order
                    $isPembelian = $workOrder->jenisWorkOrder && strtolower($workOrder->jenisWorkOrder->nama_jenis_wo) === 'pembelian';
                    
                    if ($isPembelian && $workOrder->unit) {
                        // Parse dari unit (format: "Barang (qty: 5)")
                        $unitData = $workOrder->unit;
                        if (is_string($unitData)) {
                            if (strpos($unitData, ',') !== false) {
                                $parts = explode(',', $unitData);
                                foreach ($parts as $part) {
                                    $part = trim($part);
                                    $qtyMatch = [];
                                    preg_match('/\(qty:\s*(\d+)\)/', $part, $qtyMatch);
                                    if (!empty($qtyMatch)) {
                                        $qty = (int)$qtyMatch[1];
                                        $namaBarang = trim(preg_replace('/\s*\(qty:\s*\d+\)/', '', $part));
                                    } else {
                                        $qty = 1;
                                        $namaBarang = $part;
                                    }
                                    $daftarBarangArray[] = [
                                        'nama_barang' => $namaBarang,
                                        'jumlah' => $qty,
                                        'satuan' => '-'
                                    ];
                                }
                            } else {
                                $qtyMatch = [];
                                preg_match('/\(qty:\s*(\d+)\)/', $unitData, $qtyMatch);
                                if (!empty($qtyMatch)) {
                                    $qty = (int)$qtyMatch[1];
                                    $namaBarang = trim(preg_replace('/\s*\(qty:\s*\d+\)/', '', $unitData));
                                } else {
                                    $qty = 1;
                                    $namaBarang = trim($unitData);
                                }
                                $daftarBarangArray[] = [
                                    'nama_barang' => $namaBarang,
                                    'jumlah' => $qty,
                                    'satuan' => '-'
                                ];
                            }
                        }
                    }
                }
            }
            
            // 4. Jika belum ada permintaan barang, buat permintaan barang
            // Cek ulang apakah permintaan barang sudah ada (untuk menghindari race condition)
            $permintaanBarang = PermintaanBarang::where('id_surat_pengajuan', $id)->first();
            
            if (!$permintaanBarang && !empty($daftarBarangArray)) {
                $statusDiterimaId = StatusWo::where('nama_status', 'Diterima Logistik')->value('id_status_wo');
                
                // Gunakan database transaction untuk memastikan atomicity dan menghindari race condition
                $permintaanBarang = DB::transaction(function () use ($id, $statusDiterimaId, $daftarBarangArray) {
                    // Cek ulang dalam transaction dengan lock
                    $existingPermintaan = PermintaanBarang::where('id_surat_pengajuan', $id)->lockForUpdate()->first();
                    if ($existingPermintaan) {
                        return $existingPermintaan;
                    }
                    
                    // Generate nomor permintaan (sudah menggunakan transaction dan lock di dalam method)
                    $noPermintaan = $this->generateNoPermintaan();
                    
                    // Buat permintaan barang
                    $permintaanBarang = PermintaanBarang::create([
                        'no_permintaan_barang' => $noPermintaan,
                        'id_surat_pengajuan' => $id,
                        'tanggal_permintaan' => date('Y-m-d'),
                        'status' => 'Diterima Logistik',
                        'id_status_wo' => $statusDiterimaId,
                        'total_estimasi_harga' => 0,
                        'catatan_logistik' => 'Permintaan dibuat otomatis karena stock mencukupi',
                        'id_logistik' => Session::get('user_id'),
                        'id_akun' => Session::get('user_id'),
                    ]);
                    
                    return $permintaanBarang;
                });
                
                $isNewPermintaan = true;
                
                // Simpan detail barang hanya jika permintaan baru dibuat
                if ($isNewPermintaan) {
                    foreach ($daftarBarangArray as $item) {
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
                            'id_permintaan_barang' => $permintaanBarang->id_permintaan_barang,
                            'id_daftar_barang_master' => $master->id_daftar_barang,
                            'nama_barang' => $item['nama_barang'],
                            'jumlah' => $item['jumlah'],
                            'satuan' => $item['satuan'] ?? null,
                            'estimasi_harga' => null,
                        ]);
                    }
                }
            } else if ($permintaanBarang) {
                // Jika sudah ada, pastikan status sudah benar
                $statusDiterimaId = StatusWo::where('nama_status', 'Diterima Logistik')->value('id_status_wo');
                if (!$permintaanBarang->id_status_wo) {
                    $permintaanBarang->update([
                        'id_status_wo' => $statusDiterimaId,
                        'status' => 'Diterima Logistik'
                    ]);
                }
            }
            
            Session::flash('success', 'Work Order berhasil disetujui dan barang siap diserahkan!');
            Session::flash('from_crud', true);
            
            return response()->json([
                'success' => true,
                'message' => 'Work Order berhasil diproses!',
                'redirect' => route('logistik.serahkan-barang', ['from' => 'crud'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Edit permintaan barang page.
     */
    public function editPermintaanBarang($id)
    {
        $permintaan = PermintaanBarang::with(['suratPengajuan', 'daftarBarang'])->findOrFail($id);
        $userId = Session::get('user_id');
        
        // Validasi: hanya bisa edit jika dibuat oleh user yang sama
        if ($permintaan->id_logistik != $userId) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah data ini.');
        }
        
        // Validasi: hanya bisa edit jika status "Menunggu Logistik" atau "Menunggu Purchasing"
        $statusMenungguLogistikId = StatusWo::where('nama_status', 'Menunggu Logistik')->value('id_status_wo');
        $statusMenungguPurchasingId = StatusWo::where('nama_status', 'Menunggu Purchasing')->value('id_status_wo');
        
        $allowedStatusIds = array_filter([$statusMenungguLogistikId, $statusMenungguPurchasingId]);
        
        if (!in_array($permintaan->id_status_wo, $allowedStatusIds)) {
            return redirect()->route('logistik.permintaan-barang')
                ->with('error', 'Permintaan barang tidak dapat diedit karena status sudah berubah.');
        }
        
        $workOrder = $permintaan->suratPengajuan;
        return view('logistik.edit_permintaan_barang', compact('permintaan', 'workOrder'));
    }
    
    /**
     * Update permintaan barang.
     */
    public function updatePermintaanBarang(Request $request, $id)
    {
        $request->validate([
            'tanggal_permintaan' => 'required|date',
            'daftar_barang' => 'required|string',
            'total_estimasi_harga' => 'required',
            'catatan_logistik' => 'nullable|string',
        ]);
        
        try {
            $permintaan = PermintaanBarang::findOrFail($id);
            $userId = Session::get('user_id');
            
            // Validasi: hanya bisa update jika dibuat oleh user yang sama
            if ($permintaan->id_logistik != $userId) {
                return back()->with('error', 'Anda tidak memiliki akses untuk mengubah data ini.')->withInput();
            }
            
            // Validasi: hanya bisa update jika status "Menunggu Logistik" atau "Menunggu Purchasing"
            $statusMenungguLogistikId = StatusWo::where('nama_status', 'Menunggu Logistik')->value('id_status_wo');
            $statusMenungguPurchasingId = StatusWo::where('nama_status', 'Menunggu Purchasing')->value('id_status_wo');
            
            $allowedStatusIds = array_filter([$statusMenungguLogistikId, $statusMenungguPurchasingId]);
            
            if (!in_array($permintaan->id_status_wo, $allowedStatusIds)) {
                return redirect()->route('logistik.permintaan-barang')
                    ->with('error', 'Permintaan barang tidak dapat diupdate karena status sudah berubah.');
            }
            
            // Parse total_estimasi_harga jika ada format currency
            $totalHarga = $request->total_estimasi_harga;
            if (is_string($totalHarga)) {
                $totalHarga = floatval(str_replace(['Rp ', '.', ','], '', $totalHarga));
            }
            
            // Validate daftar_barang is valid JSON
            $daftarBarangJson = $request->daftar_barang;
            $decoded = [];
            if (is_string($daftarBarangJson)) {
                $decoded = json_decode($daftarBarangJson, true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                    return back()->with('error', 'Format daftar barang tidak valid!')->withInput();
                }
            }
            
            // Update header permintaan barang
            $permintaan->update([
                'tanggal_permintaan' => $request->tanggal_permintaan,
                'total_estimasi_harga' => $totalHarga,
                'catatan_logistik' => $request->catatan_logistik,
            ]);
            
            // Hapus detail barang lama
            DetailBarangPermintaan::where('id_permintaan_barang', $permintaan->id_permintaan_barang)->delete();
            
            // Simpan detail barang baru
            if (is_array($decoded)) {
                foreach ($decoded as $item) {
                    if (!isset($item['nama_barang']) || $item['nama_barang'] === '') {
                        continue;
                    }
                    
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
                        'jumlah' => isset($item['jumlah']) ? (int) $item['jumlah'] : 0,
                        'satuan' => $item['satuan'] ?? null,
                        'estimasi_harga' => isset($item['estimasi_harga']) ? (float) $item['estimasi_harga'] : null,
                    ]);
                }
            }
            
            return redirect()->route('logistik.permintaan-barang', ['from' => 'crud'])->with('success', 'Data berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui permintaan barang: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Delete permintaan barang.
     */
    public function destroyPermintaanBarang($id)
    {
        try {
            $permintaan = PermintaanBarang::findOrFail($id);
            $userId = Session::get('user_id');
            
            // Validasi: hanya bisa delete jika dibuat oleh user yang sama
            if ($permintaan->id_logistik != $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menghapus data ini.'
                ], 403);
            }
            
            // Validasi: hanya bisa delete jika status "Menunggu Logistik" atau "Menunggu Purchasing"
            $statusMenungguLogistikId = StatusWo::where('nama_status', 'Menunggu Logistik')->value('id_status_wo');
            $statusMenungguPurchasingId = StatusWo::where('nama_status', 'Menunggu Purchasing')->value('id_status_wo');
            
            $allowedStatusIds = array_filter([$statusMenungguLogistikId, $statusMenungguPurchasingId]);
            
            if (!in_array($permintaan->id_status_wo, $allowedStatusIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permintaan barang tidak dapat dihapus karena status sudah berubah.'
                ], 403);
            }
            
            // Hapus detail barang
            DetailBarangPermintaan::where('id_permintaan_barang', $permintaan->id_permintaan_barang)->delete();
            
            // Hapus permintaan barang
            $permintaan->delete();
            
            Session::flash('success', 'Data berhasil dihapus');
            Session::flash('from_crud', true);
            
            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus',
                'redirect' => route('logistik.permintaan-barang')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
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
            ->lockForUpdate() // Lock untuk update
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
                // Nomor unik ditemukan
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
     * Proses terima barang.
     */
    public function prosesTerimaBarang($id)
    {
        try {
            $permintaan = PermintaanBarang::findOrFail($id);
            $permintaan->update([
                'status' => 'Diterima Logistik',
                'updated_at' => now(),
            ]);
            
            // Update id_status_wo juga
            $statusDiterimaId = StatusWo::where('nama_status', 'Diterima Logistik')->value('id_status_wo');
            if ($statusDiterimaId) {
                $permintaan->update(['id_status_wo' => $statusDiterimaId]);
            }
            
            // Simpan success message di session untuk toast notification
            Session::flash('success', 'Barang berhasil diterima!');
            Session::flash('from_crud', true);
            
            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil diterima!',
                'redirect' => route('logistik.terima-barang', ['from' => 'crud'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menerima barang: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Proses serahkan barang.
     */
    public function prosesSerahkanBarang($id)
    {
        try {
            $permintaan = PermintaanBarang::with('daftarBarang')->findOrFail($id);
            
            // Kurangi stock barang dari daftar_barang berdasarkan detail_barang_permintaan
            if ($permintaan->daftarBarang && $permintaan->daftarBarang->count() > 0) {
                foreach ($permintaan->daftarBarang as $detail) {
                    if ($detail->id_daftar_barang_master) {
                        $masterBarang = DaftarBarang::find($detail->id_daftar_barang_master);
                        if ($masterBarang) {
                            // Kurangi stock
                            $jumlahDiserahkan = $detail->jumlah ?? 0;
                            $stokSekarang = $masterBarang->stok ?? 0;
                            $stokBaru = max(0, $stokSekarang - $jumlahDiserahkan); // Pastikan tidak negatif
                            
                            $masterBarang->update([
                                'stok' => $stokBaru
                            ]);
                        }
                    } else {
                        // Jika tidak ada id_daftar_barang_master, cari berdasarkan nama_barang
                        $masterBarang = DaftarBarang::where('nama_barang', $detail->nama_barang)->first();
                        if ($masterBarang) {
                            $jumlahDiserahkan = $detail->jumlah ?? 0;
                            $stokSekarang = $masterBarang->stok ?? 0;
                            $stokBaru = max(0, $stokSekarang - $jumlahDiserahkan);
                            
                            $masterBarang->update([
                                'stok' => $stokBaru
                            ]);
                        }
                    }
                }
            }
            
            // Update status permintaan barang
            $statusDiserahkanId = StatusWo::where('nama_status', 'Diserahkan ke Divisi')->value('id_status_wo');
            $permintaan->update([
                'status' => 'Diserahkan ke Divisi',
                'id_status_wo' => $statusDiserahkanId,
                'updated_at' => now(),
            ]);
            
            // Simpan success message di session untuk toast notification
            Session::flash('success', 'Barang berhasil diserahkan!');
            Session::flash('from_crud', true);
            
            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil diserahkan!',
                'redirect' => route('logistik.serahkan-barang', ['from' => 'crud'])
            ]);
        } catch (\Exception $e) {
            Session::flash('error', 'Gagal menyerahkan barang: ' . $e->getMessage());
            Session::flash('from_crud', true);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyerahkan barang: ' . $e->getMessage(),
                'redirect' => route('logistik.serahkan-barang', ['from' => 'crud'])
            ], 500);
        }
    }
    
    /**
     * Show work order (API).
     */
    public function showWorkOrder($id)
    {
        $workOrder = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder'])->findOrFail($id);
        
        // Ambil jenis_kebutuhan dan daftar_barang dari permintaan_barang jika ada
        $jenisKebutuhan = null;
        $daftarBarang = null;
        
        // Cek apakah ada permintaan barang terkait
        $permintaanBarang = PermintaanBarang::where('id_surat_pengajuan', $id)->first();
        if ($permintaanBarang) {
            // Jika ada permintaan barang, jenis kebutuhan adalah 'barang'
            $jenisKebutuhan = 'barang';
            
            // Ambil daftar barang dari detail
            $detailBarang = DetailBarangPermintaan::where('id_permintaan_barang', $permintaanBarang->id_permintaan_barang)
                ->get()
                ->map(function($item) {
                    return [
                        'nama_barang' => $item->nama_barang,
                        'qty' => $item->jumlah,
                        'quantity' => $item->jumlah,
                        'satuan' => $item->satuan
                    ];
                })
                ->toArray();
            
            if (count($detailBarang) > 0) {
                $daftarBarang = json_encode($detailBarang);
            }
        } else {
            // Jika tidak ada permintaan barang, cek jenis work order
            if ($workOrder->jenisWorkOrder && strtolower($workOrder->jenisWorkOrder->nama_jenis_wo) === 'pembelian') {
                $jenisKebutuhan = 'barang';
            } else {
                $jenisKebutuhan = 'jasa';
            }
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
            'unit_code' => $workOrder->unit_code ?? $workOrder->unit,
            'id_unit' => $workOrder->id_unit,
            'uraian' => $workOrder->uraian,
            'dokumentasi' => $workOrder->dokumentasi,
            'status' => $workOrder->verifikator ? $workOrder->verifikator->nama_status : ($workOrder->status ?? 'Menunggu'),
            'id_verifikator' => $workOrder->id_verifikator,
            'jenis_kebutuhan' => $jenisKebutuhan,
            'daftar_barang' => $daftarBarang,
        ];
        
        return response()->json($data);
    }

    /**
     * Get all daftar barang stock (API).
     */
    public function getAllDaftarBarangStock()
    {
        try {
            $daftarBarang = DaftarBarang::select('id_daftar_barang', 'nama_barang', 'stok', 'satuan')
                ->orderBy('nama_barang', 'asc')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $daftarBarang
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data stock: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Render work order list page with shared configuration.
     */
    private function renderWorkOrderList(string $type = 'masuk', string $view = 'logistik.work_order_list', string $pageTitle = 'Work Order Masuk')
    {
        $divisiId = Session::get('user_divisi');
        $divisiNama = Divisi::where('id_divisi', $divisiId)->value('nama_divisi') ?? 'Logistik';

        if ($type === 'riwayat') {
            $workOrders = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder'])
                ->where(function ($query) use ($divisiNama) {
                    $query->where('ditujukan', $divisiNama)
                        ->orWhere('divisi_pengaju', $divisiNama);
                })
                ->whereIn('id_verifikator', [2, 3]) // Status Disetujui (2) dan Ditolak (3)
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $workOrders = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder'])
                ->where('ditujukan', $divisiNama)
                ->where('divisi_pengaju', '!=', $divisiNama)
                ->where('id_verifikator', 1) // Hanya status Menunggu (1)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view($view, [
            'workOrders' => $workOrders,
            'pageTitle' => $pageTitle,
            'breadcrumbLabel' => $pageTitle,
            'workOrderApiUrl' => route('logistik.api.work-order', ['id' => '__ID__']),
            'allowCreatePermintaan' => $type === 'masuk',
        ]);
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
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menyetujui work order ini.'
                ], 403);
            }
            
            $workOrder->update([
                'id_verifikator' => 2, // 2 = Disetujui
                'status' => 'Disetujui'
            ]);
            
            // Set session message untuk notifikasi toast
            Session::flash('success', 'Work Order berhasil disetujui!');
            
            // Return JSON untuk AJAX dengan redirect URL yang sudah include from=crud
            $redirectUrl = route('logistik.daftar-work-order', ['from' => 'crud']);
            
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
                'redirect' => route('logistik.daftar-work-order', ['from' => 'crud'])
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
                    'redirect' => route('logistik.daftar-work-order', ['from' => 'crud'])
                ], 403);
            }
            
            $workOrder->update([
                'id_verifikator' => 3, // 3 = Ditolak
                'status' => 'Ditolak'
            ]);
            
            // Set session message untuk notifikasi toast
            Session::flash('success', 'Work Order berhasil ditolak!');
            
            // Return JSON untuk AJAX dengan redirect URL yang sudah include from=crud
            $redirectUrl = route('logistik.daftar-work-order', ['from' => 'crud']);
            
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
                'redirect' => route('logistik.daftar-work-order', ['from' => 'crud'])
            ], 500);
        }
    }

    /**
     * Print work order.
     */
    public function cetak($id)
    {
        $workOrder = SuratPengajuan::with(['divisi', 'unit', 'verifikator', 'akun', 'jenisWorkOrder'])
            ->findOrFail($id);
        
        return view('work_order.cetak', compact('workOrder'));
    }

    private function generateWorkOrderNumber(string $prefix = 'LOG')
    {
        $year = date('Y');
        $month = date('m');

        $lastWorkOrder = SuratPengajuan::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('no_surat_pengajuan', 'like', "%/{$prefix}/%")
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastWorkOrder) {
            $lastNumber = explode('/', $lastWorkOrder->no_surat_pengajuan)[0];
            $sequence = str_pad((int)$lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $sequence = '001';
        }

        return "{$sequence}/{$prefix}/KCE/{$year}";
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
        
        return view('logistik.profile', compact('user'));
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
            
            return redirect()->route('logistik.profile', ['from' => 'crud'])->with('success', 'Data berhasil diperbarui');
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
            
            return redirect()->route('logistik.profile', ['from' => 'crud'])->with('success', 'Password berhasil diubah!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengubah password: ' . $e->getMessage());
        }
    }
}