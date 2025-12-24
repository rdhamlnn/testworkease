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
use App\Models\Akun;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LogistikController extends Controller
{
    use \App\Traits\WorkOrderActions, \App\Traits\UserProfileActions;

    /**
     * Get the view name for profile.
     */
    protected function getProfileView()
    {
        return 'logistik.profile';
    }

    /**
     * Display the dashboard page.
     */
    public function dashboard()
    {
        $userId = Session::get('user_id');
        $userDivisi = Session::get('user_divisi');
        $userDivisiNama = DB::table('divisi')->where('id_divisi', $userDivisi)->value('nama_divisi') ?? 'Logistik';
        
        // Statistik
        $totalWODicek = SuratPengajuan::count();
        $totalPermintaan = PermintaanBarang::count();
        $barangMasuk = PermintaanBarang::forStatus('Diterima Logistik')->count();
        $barangKeluar = PermintaanBarang::forStatus('Diserahkan ke Divisi')->count();
        
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
        $recentActivities = SuratPengajuan::with('akun.karyawan')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('logistik.dashboard', compact(
            'totalWODicek', 'totalPermintaan', 'barangMasuk', 'barangKeluar',
            'monthlyWOTrend', 'statusPermintaan', 'recentActivities'
        ));
    }

    
    /**
     * Display work order masuk page.
     * Menampilkan WO yang dibuat oleh Logistik ATAU WO yang dikembalikan ke Logistik untuk dikirim ulang
     */
    public function workOrder()
    {
        $userDivisiNama = DB::table('divisi')->where('id_divisi', Session::get('user_divisi'))->value('nama_divisi') ?? 'Logistik';
        
        // Tampilkan WO yang:
        // 1. Dibuat oleh divisi ini (divisi_pengaju = Logistik), ATAU
        // 2. Dikembalikan ke divisi ini untuk dikirim ulang (ditujukan = Logistik dan status berisi 'Ditolak')
        $workOrders = SuratPengajuan::with(['unit', 'jenisWorkOrder', 'verifikator'])
            ->where(function($query) use ($userDivisiNama) {
                // WO yang dibuat oleh Logistik
                $query->where('divisi_pengaju', $userDivisiNama);
            })
            ->orWhere(function($query) use ($userDivisiNama) {
                // WO yang dikembalikan ke Logistik untuk diajukan ulang
                $query->where('ditujukan', $userDivisiNama)
                      ->where('divisi_pengaju', $userDivisiNama)
                      ->where('status', 'LIKE', '%Ditolak%');
            })
            ->orderBy('created_at', 'desc')
            ->get();
        
        $divisiTujuan = Divisi::where('nama_divisi', '!=', 'Administrator')->orderBy('nama_divisi')->get();
        $unit = Unit::orderBy('nama_unit')->get();
        $jenisWorkOrder = JenisWorkOrder::all();
        $daftarBarang = DaftarBarang::orderBy('nama_barang')->get();
        $nextWorkOrderNumber = $this->generateWorkOrderNumber('LOG');
        $divisiPengaju = $userDivisiNama;

        return view('logistik.work_order', compact('userDivisiNama', 'divisiTujuan', 'unit', 'jenisWorkOrder', 'daftarBarang', 'nextWorkOrderNumber', 'workOrders', 'divisiPengaju'));
    }

    public function daftarWorkOrder()
    {
        $userDivisiNama = DB::table('divisi')->where('id_divisi', Session::get('user_divisi'))->value('nama_divisi') ?? 'Logistik';
        
        $workOrders = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder', 'parent'])
            ->toDivisi($userDivisiNama)
            ->where('divisi_pengaju', '!=', $userDivisiNama)
            ->byVerifikator(1)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('logistik.daftar_work_order', compact('workOrders'));
    }

    public function riwayatWorkOrder()
    {
        $userDivisiNama = DB::table('divisi')->where('id_divisi', Session::get('user_divisi'))->value('nama_divisi') ?? 'Logistik';
        
        $workOrders = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder', 'parent'])
            ->dibuatAtauDiterima($userDivisiNama)
            ->byVerifikator([2, 3])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('logistik.riwayat_work_order', compact('workOrders'));
    }


    /**
     * Store new work order submission.
     */
    public function storeWorkOrder(Request $request)
    {
        $request->validate([
            'id_jenis_wo' => 'required|exists:jenis_work_order,id_jenis_wo',
            'tanggal' => 'required|date',
            'unit' => 'required|string',
            'uraian' => 'required|string',
            'dokumentasi' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        try {
            $unitId = Unit::where('nama_unit', $request->unit)->value('id_unit') ?: 1;
            $userDivisiNama = DB::table('divisi')->where('id_divisi', Session::get('user_divisi'))->value('nama_divisi') ?? 'Logistik';

            $dokumentasiPath = $request->hasFile('dokumentasi') ? $this->handleUpload($request->file('dokumentasi'), 'work-orders') : null;

            $workOrder = SuratPengajuan::create([
                'no_surat_pengajuan' => $this->generateWorkOrderNumber('LOG'),
                'ditujukan' => $request->ditujukan,
                'id_jenis_wo' => $request->id_jenis_wo,
                'tanggal' => $request->tanggal,
                'divisi_pengaju' => $userDivisiNama,
                'unit' => $request->unit,
                'uraian' => $request->uraian,
                'dokumentasi' => $dokumentasiPath,
                'status' => 'Menunggu',
                'id_divisi' => Session::get('user_divisi'),
                'id_peran' => Session::get('user_peran'),
                'id_verifikator' => 1,
                'id_akun' => Session::get('user_id'),
                'id_unit' => $unitId,
            ]);

            $this->processPermintaanBarangFromRequest($request, $workOrder);

            return redirect()->route('logistik.work-order', ['from' => 'crud'])->with('success', 'Data berhasil ditambahkan');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage())->withInput();
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
            'id_jenis_wo' => 'required|exists:jenis_work_order,id_jenis_wo',
            'tanggal' => 'required|date',
            'unit' => 'required',
            'uraian' => 'required|string',
            'dokumentasi' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // Jika unit adalah array (dari Select2 multiple), konversi ke string
        $unitValue = $request->unit;
        if (is_array($unitValue)) {
            $unitValue = implode(', ', $unitValue);
        }

        try {
            $workOrder = SuratPengajuan::findOrFail($id);
            if (in_array($workOrder->id_verifikator, [2, 3])) {
                return redirect()->back()->with('error', 'Work Order sudah diproses.');
            }

            $dokumentasiPath = $workOrder->dokumentasi;
            if ($request->has('delete_dokumentasi') && $request->delete_dokumentasi == '1') {
                $this->deleteFile($workOrder->dokumentasi);
                $dokumentasiPath = null;
            } elseif ($request->hasFile('dokumentasi')) {
                $dokumentasiPath = $this->handleUpload($request->file('dokumentasi'), 'work-orders', $workOrder->dokumentasi);
            }

            $unitId = Unit::where('nama_unit', $unitValue)->value('id_unit') ?: 1;

            $workOrder->update([
                'ditujukan' => $request->ditujukan,
                'id_jenis_wo' => $request->id_jenis_wo,
                'tanggal' => $request->tanggal,
                'unit' => $unitValue,
                'uraian' => $request->uraian,
                'dokumentasi' => $dokumentasiPath,
                'id_unit' => $unitId,
            ]);

            return redirect()->route('logistik.work-order', ['from' => 'crud'])->with('success', 'Work Order berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage())->withInput();
        }
    }


    /**
     * Hapus work order milik logistik.
     */
    public function destroyWorkOrder($id)
    {
        return $this->hapusWorkOrder($id);
    }

    /**
     * Private helper to process barang items from request.
     */
    private function processPermintaanBarangFromRequest(Request $request, $workOrder)
    {
        if ($request->has('barang') && is_array($request->barang) && count($request->barang) > 0) {
            $barangItems = array_filter($request->barang, function($item) {
                return !empty($item['nama_barang']) && !empty($item['jumlah']);
            });
            
            if (count($barangItems) > 0) {
                $totalHarga = 0;
                foreach ($barangItems as $item) {
                    $totalHarga += ($item['estimasi_harga'] ?? 0) * ($item['jumlah'] ?? 0);
                }
                
                $permintaan = PermintaanBarang::create([
                    'no_permintaan_barang' => $this->generateNoPermintaan('LOG'),
                    'id_surat_pengajuan' => $workOrder->id_surat_pengajuan,
                    'tanggal_permintaan' => $request->tanggal,
                    'status' => 'Menunggu Logistik',
                    'id_status_wo' => 1,
                    'total_estimasi_harga' => $totalHarga,
                    'id_akun' => Session::get('user_id', 1),
                ]);
                
                foreach ($barangItems as $item) {
                    $master = DaftarBarang::firstOrCreate(
                        ['nama_barang' => $item['nama_barang'], 'satuan' => $item['satuan'] ?? null],
                        ['stok' => 0]
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
                'id_status_wo' => 1,
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
     * Proses terima barang.
     */
    public function prosesTerimaBarang($id)
    {
        try {
            $permintaan = PermintaanBarang::with('daftarBarang')->findOrFail($id);
            
            // Tambah stok barang ke daftar_barang berdasarkan detail_barang_permintaan
            if ($permintaan->daftarBarang && $permintaan->daftarBarang->count() > 0) {
                foreach ($permintaan->daftarBarang as $detail) {
                    if ($detail->id_daftar_barang_master) {
                        $masterBarang = DaftarBarang::find($detail->id_daftar_barang_master);
                        if ($masterBarang) {
                            // Tambah stock
                            $jumlahDiterima = $detail->jumlah ?? 0;
                            $stokSekarang = $masterBarang->stok ?? 0;
                            $stokBaru = $stokSekarang + $jumlahDiterima; // Tambah stok
                            
                            $masterBarang->update([
                                'stok' => $stokBaru
                            ]);
                        }
                    } else {
                        // Jika tidak ada id_daftar_barang_master, cari berdasarkan nama_barang
                        $masterBarang = DaftarBarang::where('nama_barang', $detail->nama_barang)->first();
                        if ($masterBarang) {
                            $jumlahDiterima = $detail->jumlah ?? 0;
                            $stokSekarang = $masterBarang->stok ?? 0;
                            $stokBaru = $stokSekarang + $jumlahDiterima; // Tambah stok
                            
                            $masterBarang->update([
                                'stok' => $stokBaru
                            ]);
                        }
                    }
                }
            }
            
            // Update status
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
            Session::flash('success', 'Barang berhasil diterima dan stok telah diupdate!');
            Session::flash('from_crud', true);
            
            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil diterima dan stok telah diupdate!',
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
     * Forward work order to Purchasing (create new WO with parent relationship).
     */
    public function forwardWorkOrderToPurchasing(Request $request, $id)
    {
        try {
            $parentWorkOrder = SuratPengajuan::with('jenisWorkOrder')->findOrFail($id);
            $userDivisi = Session::get('user_divisi');
            $userDivisiNama = DB::table('divisi')->where('id_divisi', $userDivisi)->value('nama_divisi');
            
            // Validasi: hanya bisa forward jika work order sudah disetujui dan ditujukan ke Logistik
            if ($parentWorkOrder->ditujukan !== $userDivisiNama) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk memforward work order ini.'
                ], 403);
            }
            
            if ($parentWorkOrder->id_verifikator != 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Work order harus disetujui terlebih dahulu sebelum dapat diforward ke Purchasing.'
                ], 403);
            }
            
            // Cek apakah sudah ada child work order ke Purchasing
            $existingChild = SuratPengajuan::where('id_surat_pengajuan_parent', $id)
                ->where('ditujukan', 'Purchasing')
                ->first();
            
            if ($existingChild) {
                return response()->json([
                    'success' => false,
                    'message' => 'Work order ini sudah pernah diforward ke Purchasing.'
                ], 403);
            }
            
            // Get jenis WO Pembelian
            $jenisPembelian = JenisWorkOrder::where('nama_jenis_wo', 'Pembelian')->first();
            if (!$jenisPembelian) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis Work Order "Pembelian" tidak ditemukan.'
                ], 404);
            }
            
            // Generate work order number untuk Purchasing
            $nextNoWO = $this->generateWorkOrderNumber('LOG');
            
            // Create new work order to Purchasing
            $newWorkOrder = SuratPengajuan::create([
                'no_surat_pengajuan' => $nextNoWO,
                'ditujukan' => 'Purchasing',
                'id_jenis_wo' => $jenisPembelian->id_jenis_wo,
                'tanggal' => $parentWorkOrder->tanggal,
                'divisi_pengaju' => $userDivisiNama,
                'unit' => $parentWorkOrder->unit,
                'uraian' => $parentWorkOrder->uraian . ' (Diteruskan dari ' . $parentWorkOrder->no_surat_pengajuan . ')',
                'dokumentasi' => $parentWorkOrder->dokumentasi,
                'status' => 'Menunggu',
                'id_divisi' => $userDivisi,
                'id_peran' => Session::get('user_peran'),
                'id_verifikator' => 1, // Menunggu
                'id_akun' => Session::get('user_id'),
                'id_unit' => $parentWorkOrder->id_unit,
                'id_surat_pengajuan_parent' => $id, // Link to parent
            ]);
            
            Session::flash('success', 'Work Order berhasil diforward ke Purchasing!');
            
            return response()->json([
                'success' => true,
                'message' => 'Work Order berhasil diforward ke Purchasing!',
                'redirect' => route('logistik.daftar-work-order', ['from' => 'crud'])
            ]);
        } catch (\Exception $e) {
            Session::flash('error', 'Gagal memforward work order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memforward work order: ' . $e->getMessage(),
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

    /**
     * Print work order as PDF.
     */
    public function cetakpdf($id)
    {
        $wo = SuratPengajuan::with([
            'jenisWorkOrder',
            'verifikator',
            'akun.karyawan',
            'akun.divisi',
            'divisiPengaju',
            'unit'
        ])->findOrFail($id);

        // Akun pembuat WO
        $dibuatOleh = $wo->akun;

        // Akun divisi tujuan berdasarkan nama divisi di kolom 'ditujukan'
        $diketahuiOleh = Akun::with(['karyawan', 'divisi'])
            ->whereHas('divisi', function ($q) use ($wo) {
                $q->where('nama_divisi', $wo->ditujukan);
            })
            ->first();

        // Status badge
        $wo->status_text = $wo->verifikator->nama_status ?? $wo->status ?? 'Menunggu';

        $pdf = Pdf::loadView('admin.cetak_work_order_pdf', [
            'wo' => $wo,
            'dibuatOleh' => $dibuatOleh,
            'diketahuiOleh' => $diketahuiOleh,
        ])->setPaper('A4', 'portrait');

        $cleanNo = str_replace(['/', '\\'], '-', $wo->no_surat_pengajuan);
        $filename = "WorkOrder_{$cleanNo}.pdf";

        return $pdf->stream($filename);
    }


    

}