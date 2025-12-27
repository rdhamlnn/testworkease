<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\PermintaanBarang;
use App\Models\StatusWo;
use App\Models\SuratPengajuan;
use App\Models\Divisi;
use App\Models\Unit;
use App\Models\JenisWorkOrder;
use App\Models\DaftarBarang;
use App\Models\DetailBarangPermintaan;
use App\Models\DaftarPembelianBarang;
use Carbon\Carbon;

class PurchasingController extends Controller
{
    use \App\Traits\WorkOrderActions, \App\Traits\UserProfileActions;

    /**
     * Get the view name for profile.
     */
    protected function getProfileView()
    {
        return 'purchasing.profile';
    }

    /**
     * Helper to get status ID by name.
     */
    private function getStatusId(string $statusName)
    {
        return StatusWo::where('nama_status', $statusName)->value('id_status_wo');
    }

    /**
     * Display the dashboard page.
     */
    public function dashboard()
    {
        $userId = Session::get('user_id');
        
        // Ambil id status dari master status_wo
        $statusMenungguPurchasingId = $this->getStatusId('Menunggu Purchasing');
        $statusMenungguApprovalId = $this->getStatusId('Menunggu Approval Atasan');
        $statusDisetujuiId = $this->getStatusId('Disetujui Atasan');
        $statusDibeliId = $this->getStatusId('Dibeli Purchasing');
        $statusDikirimId = $this->getStatusId('Dikirim Purchasing');
        
        // Status yang relevan untuk Purchasing
        $purchasingStatusIds = array_filter([
            $statusMenungguPurchasingId,
            $statusMenungguApprovalId,
            $statusDisetujuiId,
            $statusDibeliId,
            $statusDikirimId,
        ]);

        // Statistik
        $totalPermintaan = PermintaanBarang::where('id_status_wo', $statusMenungguPurchasingId)->count();
        $menungguApproval = PermintaanBarang::where('id_status_wo', $statusMenungguApprovalId)->count();
        $dibeli = PermintaanBarang::where('id_status_wo', $statusDibeliId)->count();
        $dikirim = PermintaanBarang::where('id_status_wo', $statusDikirimId)->count();
        
        // Chart data - Trend permintaan yang diproses Purchasing
        $monthlyPermintaanTrend = PermintaanBarang::select(
                DB::raw('MONTH(tanggal_permintaan) as month'),
                DB::raw('YEAR(tanggal_permintaan) as year'),
                DB::raw('count(*) as total')
            )
            ->whereIn('id_status_wo', $purchasingStatusIds)
            ->where('tanggal_permintaan', '>=', DB::raw('DATE_SUB(NOW(), INTERVAL 6 MONTH)'))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();
        
        $statusIds = StatusWo::whereIn('nama_status', [
                'Menunggu Purchasing',
                'Menunggu Approval Atasan',
                'Disetujui Atasan',
                'Dibeli Purchasing',
                'Dikirim Purchasing',
            ])
            ->pluck('id_status_wo', 'nama_status');

        $statusPembelian = PermintaanBarang::select('id_status_wo', DB::raw('count(*) as total'))
            ->whereIn('id_status_wo', $statusIds->values())
            ->groupBy('id_status_wo')
            ->get()
            ->mapWithKeys(function ($row) use ($statusIds) {
                $nama = $statusIds->flip()[$row->id_status_wo] ?? 'Unknown';
                return [$nama => $row->total];
            })
            ->toArray();
        
        // Recent activities - permintaan yang relevan untuk Purchasing
        $recentActivities = PermintaanBarang::with(['suratPengajuan', 'akun.karyawan', 'statusWo'])
            ->whereIn('id_status_wo', $purchasingStatusIds)
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('purchasing.dashboard', compact(
            'totalPermintaan', 'menungguApproval', 'dibeli', 'dikirim',
            'monthlyPermintaanTrend', 'statusPembelian', 'recentActivities'
        ));
    }


    /**
     * Display work order masuk page.
     * Menampilkan WO yang dibuat oleh Purchasing ATAU WO yang ditolak Atasan dan dikembalikan ke Purchasing
     */
    public function workOrder()
    {
        $userDivisiNama = DB::table('divisi')->where('id_divisi', Session::get('user_divisi'))->value('nama_divisi') ?? 'Purchasing';
        
        // Tampilkan WO yang:
        // 1. Dibuat oleh Purchasing
        // 2. Ditolak Atasan dan dikembalikan ke Purchasing (status mengandung 'Ditolak Atasan')
        $workOrders = SuratPengajuan::with(['unit', 'jenisWorkOrder', 'verifikator', 'permintaanBarang'])
            ->where(function($query) use ($userDivisiNama) {
                // WO yang dibuat oleh Purchasing
                $query->where('divisi_pengaju', $userDivisiNama);
            })
            ->orWhere(function($query) use ($userDivisiNama) {
                // WO yang ditolak Atasan dan dikembalikan ke Purchasing
                $query->where('ditujukan', $userDivisiNama)
                      ->where('status', 'LIKE', '%Ditolak Atasan%');
            })
            ->orderBy('created_at', 'desc')
            ->get();
        
        $divisiTujuan = Divisi::where('nama_divisi', '!=', 'Administrator')->orderBy('nama_divisi')->get();
        $unit = Unit::orderBy('nama_unit')->get();
        $jenisWorkOrder = JenisWorkOrder::all();
        $nextWorkOrderNumber = $this->generateWorkOrderNumber('PUR');
        $daftarBarang = DaftarBarang::orderBy('nama_barang')->get();
        $divisiPengaju = $userDivisiNama;

        return view('purchasing.work_order', compact('userDivisiNama', 'divisiTujuan', 'unit', 'jenisWorkOrder', 'nextWorkOrderNumber', 'workOrders', 'daftarBarang', 'divisiPengaju'));
    }

    public function daftarWorkOrder()
    {
        $userDivisiNama = DB::table('divisi')->where('id_divisi', Session::get('user_divisi'))->value('nama_divisi') ?? 'Purchasing';
        
        // Load nested relations
        $workOrders = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder', 'parent', 'permintaanBarang.daftarBarang.masterBarang'])
            ->toDivisi($userDivisiNama)
            ->where('divisi_pengaju', '!=', $userDivisiNama)
            ->byVerifikator(1)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Preload semua harga barang untuk efisiensi
        $masterBarangPrices = DB::table('daftar_barang')
            ->whereNotNull('harga_barang')
            ->where('harga_barang', '>', 0)
            ->pluck('harga_barang', 'nama_barang')
            ->toArray();
        
        // Hitung total harga untuk setiap work order
        $workOrders->each(function ($wo) use ($masterBarangPrices) {
            $totalHarga = 0;
            
            // Prioritas 1: Hitung dari detail_barang_permintaan
            if ($wo->permintaanBarang && $wo->permintaanBarang->daftarBarang && $wo->permintaanBarang->daftarBarang->count() > 0) {
                foreach ($wo->permintaanBarang->daftarBarang as $detail) {
                    $jumlah = $detail->jumlah ?? 1;
                    
                    if ($detail->estimasi_harga && $detail->estimasi_harga > 0) {
                        $totalHarga += $detail->estimasi_harga;
                    } elseif ($detail->masterBarang && $detail->masterBarang->harga_barang && $detail->masterBarang->harga_barang > 0) {
                        $totalHarga += $detail->masterBarang->harga_barang * $jumlah;
                    }
                }
            }
            
            // Prioritas 2: Fallback ke total_estimasi_harga
            if ($totalHarga == 0 && $wo->permintaanBarang && $wo->permintaanBarang->total_estimasi_harga > 0) {
                $totalHarga = $wo->permintaanBarang->total_estimasi_harga;
            }
            
            // Prioritas 3: Parse dari field unit jika masih 0
            // Format: "Filter Oli (qty: 1), Belt Alternator (qty: 2)"
            if ($totalHarga == 0 && $wo->unit) {
                $unitString = is_object($wo->unit) ? ($wo->unit->nama_unit ?? '') : $wo->unit;
                
                // Parse items dari string unit
                // Match patterns like "Nama Barang (qty: N)" or just "Nama Barang"
                preg_match_all('/([^,]+?)(?:\s*\(qty:\s*(\d+)\))?(?:,|$)/i', $unitString, $matches, PREG_SET_ORDER);
                
                foreach ($matches as $match) {
                    $namaBarang = trim($match[1]);
                    $qty = isset($match[2]) ? (int)$match[2] : 1;
                    
                    if (empty($namaBarang)) continue;
                    
                    // Cari harga dari master barang
                    $harga = 0;
                    foreach ($masterBarangPrices as $nama => $price) {
                        if (stripos($namaBarang, $nama) !== false || stripos($nama, $namaBarang) !== false) {
                            $harga = $price;
                            break;
                        }
                    }
                    
                    if ($harga > 0) {
                        $totalHarga += $harga * $qty;
                    }
                }
            }
            
            $wo->calculated_total_harga = $totalHarga;
        });
        
        $daftarBarang = DaftarBarang::all();
        
        return view('purchasing.daftar_work_order', compact('workOrders', 'daftarBarang'));
    }

    public function riwayatWorkOrder()
    {
        $userDivisiNama = DB::table('divisi')->where('id_divisi', Session::get('user_divisi'))->value('nama_divisi') ?? 'Purchasing';
        
        $workOrders = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder', 'parent', 'daftarPembelianBarang'])
            ->where(function($query) use ($userDivisiNama) {
                // Kondisi 1: WO yang dibuat/ditujukan ke Purchasing dengan verifikator 2/3
                $query->where(function($q) use ($userDivisiNama) {
                    $q->where(function($sub) use ($userDivisiNama) {
                        $sub->where('divisi_pengaju', $userDivisiNama)
                            ->orWhere('ditujukan', $userDivisiNama);
                    })
                    ->whereIn('id_verifikator', [2, 3]);
                })
                // Kondisi 2: WO yang pernah diproses Purchasing (ada log pembelian)
                ->orWhereHas('daftarPembelianBarang')
                // Kondisi 3: WO dengan status Disetujui (sudah selesai di-serahkan)
                ->orWhere(function($q) {
                    $q->where('status', 'Disetujui')
                      ->orWhere('id_verifikator', 2);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Ambil daftar barang untuk lookup satuan
        $daftarBarang = DaftarBarang::all();
        
        return view('purchasing.riwayat_work_order', compact('workOrders', 'daftarBarang'));
    }

    /**
     * Display detail work order page.
     */
    public function detailWorkOrder($id)
    {
        $workOrder = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder', 'permintaanBarang.daftarBarang.masterBarang'])
            ->findOrFail($id);
        
        // Parse barang dan qty dari field unit untuk jenis Pembelian
        $jenisWo = $workOrder->jenisWorkOrder ? strtolower($workOrder->jenisWorkOrder->nama_jenis_wo) : '';
        $isPembelian = $jenisWo === 'pembelian';
        
        $barangItems = [];
        
        // Ambil master barang untuk lookup satuan dan harga
        $masterBarangLookup = DaftarBarang::all()->keyBy('nama_barang');
        
        if ($isPembelian && $workOrder->unit && $workOrder->unit !== '-') {
            $parts = explode(', ', $workOrder->unit);
            foreach ($parts as $part) {
                if (preg_match('/^(.+?)\s*\(qty:\s*(\d+)\)$/i', trim($part), $matches)) {
                    $namaBarang = trim($matches[1]);
                    $jumlah = (int)$matches[2];
                    $masterBarang = $masterBarangLookup->get($namaBarang);
                    $satuan = $masterBarang ? $masterBarang->satuan : '-';
                    $hargaSatuan = $masterBarang ? ($masterBarang->harga_barang ?? 0) : 0; // Corrected to harga_barang
                    $estimasiHarga = $jumlah * $hargaSatuan;
                    
                    $barangItems[] = [
                        'id_barang' => $masterBarang ? $masterBarang->id_daftar_barang : null,
                        'nama_barang' => $namaBarang,
                        'jumlah' => $jumlah,
                        'satuan' => $satuan,
                        'harga_satuan' => $hargaSatuan,
                        'estimasi_harga' => $estimasiHarga,
                    ];
                } elseif (!empty(trim($part))) {
                    $namaBarang = trim($part);
                    $masterBarang = $masterBarangLookup->get($namaBarang);
                    $satuan = $masterBarang ? $masterBarang->satuan : '-';
                    $hargaSatuan = $masterBarang ? ($masterBarang->harga_barang ?? 0) : 0; // Corrected to harga_barang
                    $estimasiHarga = 1 * $hargaSatuan;
                    
                    $barangItems[] = [
                        'id_barang' => $masterBarang ? $masterBarang->id_daftar_barang : null,
                        'nama_barang' => $namaBarang,
                        'jumlah' => 1,
                        'satuan' => $satuan,
                        'harga_satuan' => $hargaSatuan,
                        'estimasi_harga' => $estimasiHarga,
                    ];
                }
            }
        }
        
        // Jika ada data dari detail_barang_permintaan, gunakan itu sebagai prioritas
        if ($workOrder->permintaanBarang && $workOrder->permintaanBarang->daftarBarang && $workOrder->permintaanBarang->daftarBarang->count() > 0) {
            $barangItems = $workOrder->permintaanBarang->daftarBarang->map(function($detail) use ($masterBarangLookup) {
                $namaBarang = $detail->nama_barang;
                $jumlah = $detail->jumlah ?? 1;
                
                // Cari satuan dan harga dari master barang atau detail langsung
                $masterBarang = $detail->masterBarang ?? $masterBarangLookup->get($namaBarang);
                $satuan = $detail->satuan ?? ($masterBarang ? $masterBarang->satuan : '-');
                
                // harga_satuan dari detail->estimasi_harga jika ada dan > 0, atau dari master barang
                $hargaSatuan = ($detail->estimasi_harga && $detail->estimasi_harga > 0) 
                                ? ($detail->estimasi_harga / $jumlah) // If estimasi_harga is total, get unit price
                                : ($masterBarang ? ($masterBarang->harga_barang ?? 0) : 0); // Corrected to harga_barang
                
                // Jika estimasi_harga sudah tersimpan sebagai total, gunakan langsung
                // Jika tidak, hitung dari jumlah * harga satuan
                $estimasiHarga = $detail->estimasi_harga ?? ($jumlah * $hargaSatuan);
                
                return [
                    'id_barang' => $masterBarang ? $masterBarang->id_daftar_barang : null,
                    'nama_barang' => $namaBarang,
                    'jumlah' => $jumlah,
                    'satuan' => $satuan,
                    'harga_satuan' => $hargaSatuan,
                    'estimasi_harga' => $estimasiHarga,
                ];
            })->toArray();
        }
        
        // Hitung total harga dari semua item
        $totalHarga = 0;
        foreach ($barangItems as $item) {
            $totalHarga += $item['estimasi_harga'];
        }
        
        // Get status
        $status = $workOrder->verifikator->nama_status ?? $workOrder->status ?? 'Menunggu';

        // Ambil data Realisasi Pembelian (Logs)
        $realisasiItems = DaftarPembelianBarang::with('barang')
            ->where('id_surat_pengajuan', $id)
            ->get();
            
        $totalRealisasi = $realisasiItems->sum('total_harga');
        
        return view('purchasing.detail_work_order', compact('workOrder', 'barangItems', 'totalHarga', 'status', 'isPembelian', 'realisasiItems', 'totalRealisasi'));
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
            $userDivisiNama = DB::table('divisi')->where('id_divisi', Session::get('user_divisi'))->value('nama_divisi') ?? 'Purchasing';

            $dokumentasiPath = $request->hasFile('dokumentasi') ? $this->handleUpload($request->file('dokumentasi'), 'work-orders') : null;

            $workOrder = SuratPengajuan::create([
                'no_surat_pengajuan' => $this->generateWorkOrderNumber('PUR'),
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

            return redirect()->route('purchasing.work-order', ['from' => 'crud'])->with('success', 'Data berhasil ditambahkan');
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
     * Update work order Purchasing.
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

            return redirect()->route('purchasing.work-order', ['from' => 'crud'])->with('success', 'Work Order berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal: ' . $e->getMessage())->withInput();
        }
    }


    /**
     * Hapus work order Purchasing.
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
                $processedItems = [];
                
                // Pre-process items untuk mendapatkan harga dari master jika tidak ada
                foreach ($barangItems as $item) {
                    $master = DaftarBarang::firstOrCreate(
                        ['nama_barang' => $item['nama_barang'], 'satuan' => $item['satuan'] ?? null],
                        ['stok' => 0]
                    );
                    
                    // Ambil harga: prioritas dari form, fallback ke master barang
                    $hargaSatuan = isset($item['estimasi_harga']) && $item['estimasi_harga'] > 0 
                        ? (float)$item['estimasi_harga'] 
                        : ($master->harga_barang ?? 0);
                    
                    $jumlah = (int)($item['jumlah'] ?? 0);
                    $hargaTotal = $hargaSatuan * $jumlah;
                    $totalHarga += $hargaTotal;
                    
                    $processedItems[] = [
                        'master' => $master,
                        'nama_barang' => $item['nama_barang'],
                        'jumlah' => $jumlah,
                        'satuan' => $item['satuan'] ?? null,
                        'estimasi_harga' => $hargaTotal > 0 ? $hargaTotal : null,
                    ];
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
                
                foreach ($processedItems as $item) {
                    DetailBarangPermintaan::create([
                        'id_permintaan_barang' => $permintaan->id_permintaan_barang,
                        'id_daftar_barang_master' => $item['master']->id_daftar_barang,
                        'nama_barang' => $item['nama_barang'],
                        'jumlah' => $item['jumlah'],
                        'satuan' => $item['satuan'],
                        'estimasi_harga' => $item['estimasi_harga'],
                    ]);
                }
            }
        }
    }

    
    /**
     * Display permintaan barang page.
     * @deprecated View file has been removed. This route is no longer available.
     */
    public function permintaanBarang()
    {
        abort(404, 'Halaman Permintaan Barang sudah tidak tersedia.');
    }
    
    /**
     * Display beli barang page.
     */
    public function beliBarang()
    {
        $statusDisetujuiId = StatusWo::where('nama_status', 'Disetujui Atasan')->value('id_status_wo');

        $permintaanBarang = PermintaanBarang::with(['suratPengajuan', 'akun', 'statusWo', 'daftarBarang'])
            ->where('id_status_wo', $statusDisetujuiId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('purchasing.beli_barang', compact('permintaanBarang'));
    }
    
    /**
     * Display kirim barang page.
     */
    public function kirimBarang()
    {
        $statusDibeliId = StatusWo::where('nama_status', 'Dibeli Purchasing')->value('id_status_wo');

        $permintaanBarang = PermintaanBarang::with(['suratPengajuan', 'akun', 'statusWo', 'daftarBarang'])
            ->where('id_status_wo', $statusDibeliId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('purchasing.kirim_barang', compact('permintaanBarang'));
    }



    /**
     * Show permintaan barang detail (API).
     */
    public function showPermintaanBarang($id)
    {
        try {
            $permintaan = PermintaanBarang::with(['suratPengajuan', 'statusWo', 'daftarBarang'])->findOrFail($id);

            return response()->json([
                'id_permintaan_barang' => $permintaan->id_permintaan_barang,
                'no_permintaan_barang' => $permintaan->no_permintaan_barang,
                'no_work_order' => $permintaan->suratPengajuan ? $permintaan->suratPengajuan->no_surat_pengajuan : null,
                'tanggal_permintaan' => $permintaan->tanggal_permintaan,
                'total_estimasi_harga' => $permintaan->total_estimasi_harga ?? 0,
                'status' => $permintaan->statusWo ? $permintaan->statusWo->nama_status : ($permintaan->status ?? 'Menunggu'),
                'catatan_logistik' => $permintaan->catatan_logistik,
                'catatan_purchasing' => $permintaan->catatan_purchasing,
                'catatan_atasan' => $permintaan->catatan_atasan,
                'daftar_barang' => $permintaan->daftarBarang->map(function($barang) {
                    return [
                        'nama_barang' => $barang->nama_barang,
                        'jumlah' => $barang->jumlah,
                        'satuan' => $barang->satuan,
                        'estimasi_harga' => $barang->estimasi_harga ?? 0,
                    ];
                })->toArray(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Permintaan barang tidak ditemukan'], 404);
        }
    }
    
    /**
     * Edit harga permintaan barang page.
     * @deprecated View file has been removed. This route is no longer available.
     */
    public function editHargaPermintaan($id)
    {
        abort(404, 'Halaman Edit Harga Permintaan sudah tidak tersedia.');
    }
    
    /**
     * Update harga permintaan barang.
     */
    public function updateHargaPermintaan(Request $request, $id)
    {
        $request->validate([
            'harga_barang' => 'required|array',
            'harga_barang.*' => 'required|numeric|min:0',
            'catatan_purchasing' => 'nullable|string',
        ]);
        
        try {
            $permintaan = PermintaanBarang::with('daftarBarang')->findOrFail($id);
            
            // Validasi: hanya bisa update jika status "Menunggu Purchasing"
            $statusMenungguPurchasingId = StatusWo::where('nama_status', 'Menunggu Purchasing')->value('id_status_wo');
            
            if ($permintaan->id_status_wo != $statusMenungguPurchasingId) {
                return redirect()->route('purchasing.permintaan-barang')
                    ->with('error', 'Harga hanya dapat diupdate untuk permintaan dengan status Menunggu Purchasing.');
            }
            
            $totalHarga = 0;
            $hargaBarang = $request->harga_barang;
            
            // Update harga per item dan hitung total
            foreach ($permintaan->daftarBarang as $index => $detailBarang) {
                $detailId = $detailBarang->id_daftar_barang;
                
                if (isset($hargaBarang[$detailId])) {
                    $harga = (float) $hargaBarang[$detailId];
                    $detailBarang->update(['estimasi_harga' => $harga]);
                    $totalHarga += $harga * $detailBarang->jumlah;
                }
            }
            
            // Update total harga dan catatan purchasing
            $permintaan->update([
                'total_estimasi_harga' => $totalHarga,
                'catatan_purchasing' => $request->catatan_purchasing,
                'id_purchasing' => Session::get('user_id'),
            ]);
            
            return redirect()->route('purchasing.permintaan-barang', ['from' => 'crud'])
                ->with('success', 'Harga berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui harga: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Proses beli barang.
     */
    public function prosesBeliBarang($id)
    {
        try {
            $permintaan = PermintaanBarang::findOrFail($id);
            
            // Update id_status_wo juga
            $statusDibeliId = StatusWo::where('nama_status', 'Dibeli Purchasing')->value('id_status_wo');
            
            $permintaan->update([
                'status' => 'Dibeli Purchasing',
                'id_status_wo' => $statusDibeliId,
                'id_purchasing' => Session::get('user_id'),
                'updated_at' => now(),
            ]);
            
            // Simpan success message di session untuk toast notification
            Session::flash('success', 'Barang berhasil dibeli!');
            Session::flash('from_crud', true);
            
            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil dibeli!',
                'redirect' => route('purchasing.beli-barang', ['from' => 'crud'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membeli barang: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Proses kirim barang.
     */
    public function prosesKirimBarang($id)
    {
        try {
            // Load PermintaanBarang
            $permintaan = PermintaanBarang::findOrFail($id);
            
            DB::beginTransaction();

            // 1. Update status permintaan barang ke 'Dikirim Purchasing'
            // Data akan muncul di halaman Terima Barang Logistik
            $statusDikirimId = StatusWo::where('nama_status', 'Dikirim Purchasing')->value('id_status_wo');
            
            $permintaan->update([
                'status' => 'Dikirim Purchasing',
                'id_status_wo' => $statusDikirimId,
                'updated_at' => now(),
            ]);

            // 2. Update status SuratPengajuan (Work Order) jika ada
            if ($permintaan->suratPengajuan) {
                $permintaan->suratPengajuan->update([
                    'status' => 'Dikirim Purchasing',
                    'updated_at' => now()
                ]);
            }
            
            DB::commit();

            Session::flash('success', 'Barang berhasil dikirim ke Logistik!');
            Session::flash('from_crud', true);
            
            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil dikirim ke Logistik!',
                'redirect' => route('purchasing.kirim-barang', ['from' => 'crud'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim barang: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Kirim permintaan ke atasan untuk approval.
     */
    public function kirimKeAtasan($id)
    {
        try {
            $permintaan = PermintaanBarang::with('daftarBarang')->findOrFail($id);
            $statusMenungguPurchasingId = StatusWo::where('nama_status', 'Menunggu Purchasing')->value('id_status_wo');
            $statusMenungguApprovalId = StatusWo::where('nama_status', 'Menunggu Approval Atasan')->value('id_status_wo');
            
            // Validasi: hanya bisa kirim jika status "Menunggu Purchasing"
            if ($permintaan->id_status_wo != $statusMenungguPurchasingId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permintaan hanya dapat dikirim jika status Menunggu Purchasing.'
                ], 403);
            }
            
            // Validasi: pastikan ada harga yang sudah diupdate (cek apakah ada id_purchasing atau catatan_purchasing)
            // Atau bisa juga cek apakah semua detail barang sudah ada harga
            $hasHarga = $permintaan->daftarBarang->every(function($barang) {
                return $barang->estimasi_harga > 0;
            });
            
            if (!$hasHarga && $permintaan->daftarBarang->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan update harga barang terlebih dahulu sebelum mengirim ke atasan.'
                ], 403);
            }

            $permintaan->update([
                'status' => 'Menunggu Approval Atasan',
                'id_status_wo' => $statusMenungguApprovalId,
                'id_purchasing' => Session::get('user_id'),
                'updated_at' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Permintaan berhasil dikirim ke Atasan untuk approval!',
                'redirect' => route('purchasing.permintaan-barang')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim permintaan: ' . $e->getMessage()
            ], 500);
        }
    }
    


    /**
     * Render work order list for purchasing.
     */
    private function renderWorkOrderPage(string $type = 'masuk', string $view = 'purchasing.work_order_list', string $pageTitle = 'Work Order Masuk')
    {
        $divisiId = Session::get('user_divisi');
        $divisiNama = DB::table('divisi')->where('id_divisi', $divisiId)->value('nama_divisi') ?? 'Purchasing';

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
            'workOrderApiUrl' => route('purchasing.api.work-order', ['id' => '__ID__']),
        ]);
    }

    /**
     * Generate Work Order number for Purchasing.
     */
    /**
     * Approve work order.
     * Jika jenis WO = Pembelian dan ditujukan = Purchasing, maka teruskan ke Atasan
     */
    public function approveWorkOrder($id)
    {
        try {
            $workOrder = SuratPengajuan::with('jenisWorkOrder')->findOrFail($id);
            $userDivisi = Session::get('user_divisi');
            $userDivisiNama = DB::table('divisi')->where('id_divisi', $userDivisi)->value('nama_divisi');
            
            // Hanya divisi yang dituju yang bisa approve
            if ($workOrder->ditujukan !== $userDivisiNama) {
                Session::flash('error', 'Anda tidak memiliki akses untuk menyetujui work order ini.');
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menyetujui work order ini.',
                    'redirect' => route('purchasing.daftar-work-order', ['from' => 'crud'])
                ], 403);
            }
            
            // Cek jenis work order
            $jenisWo = $workOrder->jenisWorkOrder ? strtolower($workOrder->jenisWorkOrder->nama_jenis_wo) : '';
            
            // Jika jenis WO = Pembelian, teruskan ke Atasan untuk approval
            if ($jenisWo === 'pembelian') {
                $workOrder->update([
                    'ditujukan' => 'Atasan', // Teruskan ke Atasan
                    'id_verifikator' => 1, // 1 = Menunggu (reset untuk approval atasan)
                    'status' => 'Menunggu Approval Atasan'
                ]);
                
                Session::flash('success', 'Work Order berhasil diteruskan ke Atasan untuk approval!');
                $message = 'Work Order berhasil diteruskan ke Atasan untuk approval!';
            } else {
                // Untuk jenis WO lainnya, langsung disetujui
                $workOrder->update([
                    'id_verifikator' => 2, // 2 = Disetujui
                    'status' => 'Disetujui Purchasing'
                ]);
                
                Session::flash('success', 'Work Order berhasil disetujui!');
                $message = 'Work Order berhasil disetujui!';
            }
            
            // Return JSON untuk AJAX dengan redirect URL
            $redirectUrl = route('purchasing.daftar-work-order', ['from' => 'crud']);
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => $redirectUrl
            ]);
        } catch (\Exception $e) {
            Session::flash('error', 'Gagal menyetujui work order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui work order: ' . $e->getMessage(),
                'redirect' => route('purchasing.daftar-work-order', ['from' => 'crud'])
            ], 500);
        }
    }
    
    /**
     * Reject work order.
     * Jika jenis WO = Pembelian dan ditujukan = Purchasing, kembalikan ke divisi pengaju (Logistik)
     * untuk dikirim ulang
     */
    public function rejectWorkOrder($id)
    {
        try {
            $workOrder = SuratPengajuan::with('jenisWorkOrder')->findOrFail($id);
            $userDivisi = Session::get('user_divisi');
            $userDivisiNama = DB::table('divisi')->where('id_divisi', $userDivisi)->value('nama_divisi');
            
            // Hanya divisi yang dituju yang bisa reject
            if ($workOrder->ditujukan !== $userDivisiNama) {
                Session::flash('error', 'Anda tidak memiliki akses untuk menolak work order ini.');
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menolak work order ini.',
                    'redirect' => route('purchasing.daftar-work-order', ['from' => 'crud'])
                ], 403);
            }
            
            // Cek jenis work order
            $jenisWo = $workOrder->jenisWorkOrder ? strtolower($workOrder->jenisWorkOrder->nama_jenis_wo) : '';
            
            // Jika jenis WO = Pembelian, kembalikan ke divisi pengaju untuk dikirim ulang
            if ($jenisWo === 'pembelian') {
                // Kembalikan ke divisi pengaju (biasanya Logistik)
                $divisiPengaju = $workOrder->divisi_pengaju;
                
                $workOrder->update([
                    'ditujukan' => $divisiPengaju, // Kembalikan ke divisi pengaju
                    'id_verifikator' => 1, // 1 = Menunggu (reset untuk dikirim ulang)
                    'status' => 'Ditolak Purchasing - Perlu Diajukan Ulang'
                ]);
                
                Session::flash('success', "Work Order ditolak dan dikembalikan ke {$divisiPengaju} untuk diajukan ulang.");
                $message = "Work Order ditolak dan dikembalikan ke {$divisiPengaju} untuk diajukan ulang.";
            } else {
                // Untuk jenis WO lainnya, langsung ditolak
                $workOrder->update([
                    'id_verifikator' => 3, // 3 = Ditolak
                    'status' => 'Ditolak Purchasing'
                ]);
                
                Session::flash('success', 'Work Order berhasil ditolak!');
                $message = 'Work Order berhasil ditolak!';
            }
            
            // Return JSON untuk AJAX dengan redirect URL
            $redirectUrl = route('purchasing.daftar-work-order', ['from' => 'crud']);
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'redirect' => $redirectUrl
            ]);
        } catch (\Exception $e) {
            Session::flash('error', 'Gagal menolak work order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak work order: ' . $e->getMessage(),
                'redirect' => route('purchasing.daftar-work-order', ['from' => 'crud'])
            ], 500);
        }
    }

    /**
     * Update harga work order.
     */
    public function updateHargaWorkOrder(Request $request, $id)
    {
        $request->validate([
            'harga_barang' => 'required|array',
            'harga_barang.*' => 'required|numeric|min:0',
            'total_harga' => 'required|numeric|min:0',
        ]);
        
        try {
            $workOrder = SuratPengajuan::findOrFail($id);
            $userDivisi = Session::get('user_divisi');
            $userDivisiNama = DB::table('divisi')->where('id_divisi', $userDivisi)->value('nama_divisi');
            
            // Validasi: hanya bisa update jika work order ditujukan ke Purchasing dan sudah disetujui
            if ($workOrder->ditujukan !== $userDivisiNama || $workOrder->id_verifikator != 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Harga hanya dapat diupdate untuk work order yang sudah disetujui dan ditujukan ke Purchasing.'
                ], 403);
            }
            
            $workOrder->update([
                'harga_barang' => $request->harga_barang,
                'total_harga' => $request->total_harga,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Harga berhasil diperbarui!',
                'redirect' => route('purchasing.daftar-work-order', ['from' => 'crud'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui harga: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Forward work order to Atasan (create new WO with parent relationship and harga).
     */
    public function forwardWorkOrderToAtasan(Request $request, $id)
    {
        try {
            $parentWorkOrder = SuratPengajuan::with('jenisWorkOrder')->findOrFail($id);
            $userDivisi = Session::get('user_divisi');
            $userDivisiNama = DB::table('divisi')->where('id_divisi', $userDivisi)->value('nama_divisi');
            
            // Validasi: hanya bisa forward jika work order sudah disetujui dan ditujukan ke Purchasing
            if ($parentWorkOrder->ditujukan !== $userDivisiNama) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk memforward work order ini.'
                ], 403);
            }
            
            if ($parentWorkOrder->id_verifikator != 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Work order harus disetujui terlebih dahulu sebelum dapat diforward ke Atasan.'
                ], 403);
            }
            
            // Validasi: pastikan sudah ada harga
            if (!$parentWorkOrder->total_harga || $parentWorkOrder->total_harga <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan update harga barang terlebih dahulu sebelum mengirim ke Atasan.'
                ], 403);
            }
            
            // Cek apakah sudah ada child work order ke Atasan
            $existingChild = SuratPengajuan::where('id_surat_pengajuan_parent', $id)
                ->where('ditujukan', 'Atasan')
                ->first();
            
            if ($existingChild) {
                return response()->json([
                    'success' => false,
                    'message' => 'Work order ini sudah pernah diforward ke Atasan.'
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
            
            // Get Atasan divisi ID
            $atasanDivisi = Divisi::where('nama_divisi', 'Atasan')->first();
            if (!$atasanDivisi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Divisi Atasan tidak ditemukan.'
                ], 404);
            }
            
            // Generate work order number untuk Atasan
            $nextNoWO = $this->generateWorkOrderNumber('PUR');
            
            // Create new work order to Atasan
            $newWorkOrder = SuratPengajuan::create([
                'no_surat_pengajuan' => $nextNoWO,
                'ditujukan' => 'Atasan',
                'id_jenis_wo' => $jenisPembelian->id_jenis_wo,
                'tanggal' => $parentWorkOrder->tanggal,
                'divisi_pengaju' => $userDivisiNama,
                'unit' => $parentWorkOrder->unit,
                'uraian' => $parentWorkOrder->uraian . ' (Diteruskan dari ' . $parentWorkOrder->no_surat_pengajuan . ')',
                'dokumentasi' => $parentWorkOrder->dokumentasi,
                'status' => 'Menunggu',
                'id_divisi' => $atasanDivisi->id_divisi,
                'id_peran' => Session::get('user_peran'),
                'id_verifikator' => 1, // Menunggu
                'id_akun' => Session::get('user_id'),
                'id_unit' => $parentWorkOrder->id_unit,
                'id_surat_pengajuan_parent' => $id, // Link to parent
                'harga_barang' => $parentWorkOrder->harga_barang, // Copy harga
                'total_harga' => $parentWorkOrder->total_harga, // Copy total harga
            ]);
            
            Session::flash('success', 'Work Order berhasil diforward ke Atasan!');
            
            return response()->json([
                'success' => true,
                'message' => 'Work Order berhasil diforward ke Atasan!',
                'redirect' => route('purchasing.daftar-work-order', ['from' => 'crud'])
            ]);
        } catch (\Exception $e) {
            Session::flash('error', 'Gagal memforward work order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memforward work order: ' . $e->getMessage(),
                'redirect' => route('purchasing.daftar-work-order', ['from' => 'crud'])
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
            
            $sequence = str_pad((int)$sequence + 1, 3, '0', STR_PAD_LEFT);
            $attempt++;
        }
        
        // Fallback jika masih ada masalah
        return "{$sequence}/LOG/KCE/{$year}";
    }

    private function generateWorkOrderNumber(string $prefix = 'PUR')
    {
        $year = date('Y');
        $month = date('m');

        // Filter by prefix so sequence is per divisi
        $lastWorkOrder = SuratPengajuan::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('no_surat_pengajuan', 'like', "%/{$prefix}/%")
            ->orderByRaw("CAST(SUBSTRING_INDEX(no_surat_pengajuan, '/', 1) AS UNSIGNED) DESC")
            ->first();

        if ($lastWorkOrder) {
            $lastNumber = explode('/', $lastWorkOrder->no_surat_pengajuan)[0];
            $sequence = str_pad((int)$lastNumber + 1, 2, '0', STR_PAD_LEFT);
        } else {
            $sequence = '01';
        }

        return "{$sequence}/{$prefix}/KCE/{$year}";
    }

    /**
     * Resend work order yang ditolak Atasan kembali ke Atasan.
     */
    public function resendWorkOrderToAtasan(Request $request, $id)
    {
        try {
            $workOrder = SuratPengajuan::with('permintaanBarang')->findOrFail($id);
            $userDivisiNama = DB::table('divisi')->where('id_divisi', Session::get('user_divisi'))->value('nama_divisi');
            
            // Validasi: hanya WO yang statusnya "Ditolak Atasan" yang bisa dikirim ulang
            if (strpos($workOrder->status, 'Ditolak Atasan') === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya work order yang ditolak Atasan yang dapat dikirim ulang.'
                ], 403);
            }
            
            // Update work order: kirim ke Atasan
            $workOrder->update([
                'ditujukan' => 'Atasan',
                'id_verifikator' => 1, // Reset ke menunggu
                'status' => 'Menunggu Approval Atasan (Dikirim Ulang)',
                'catatan_penolakan' => null, // Reset catatan penolakan
            ]);
            
            // Update status permintaan barang jika ada
            if ($workOrder->permintaanBarang) {
                $statusMenungguId = StatusWo::where('nama_status', 'Menunggu Approval Atasan')->value('id_status_wo');
                
                $workOrder->permintaanBarang->update([
                    'status' => 'Menunggu Approval Atasan',
                    'id_status_wo' => $statusMenungguId,
                    'catatan_atasan' => null,
                ]);
            }
            
            Session::flash('success', 'Work Order berhasil dikirim ulang ke Atasan!');
            return response()->json([
                'success' => true,
                'message' => 'Work Order berhasil dikirim ulang ke Atasan!',
                'redirect' => route('purchasing.work-order', ['from' => 'crud'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim work order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Halaman Daftar Barang Work Order (Log Pembelian).
     * Menampilkan data barang yang sudah di-order setelah WO status 'Disetujui'
     * (trigger: button Serahkan Barang di Logistik)
     */
    public function daftarBarangWorkOrder()
    {
        // Tampilkan daftar barang dari Work Order yang statusnya 'Disetujui'
        // Ini adalah status setelah Logistik menekan button "Serahkan Barang"
        $pembelian = DaftarPembelianBarang::with(['barang', 'workOrder'])
            ->whereHas('workOrder', function($query) {
                $query->where('status', 'Disetujui')
                      ->orWhere('id_verifikator', 2) // 2 = Disetujui
                      ->orWhere('status', 'LIKE', '%Selesai%'); // Backward compatibility
            })
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('purchasing.daftar_barang_work_order', compact('pembelian'));
    }

    /**
     * Simpan daftar barang yang dibeli dari halaman Detail Work Order.
     * - Mengupdate field 'unit' di surat_pengajuan dengan barang yang dipilih saja
     * - Menyimpan log ke daftar_pembelian_barang
     */
    public function storeHargaBarang(Request $request)
    {
        $request->validate([
            'id_surat_pengajuan' => 'required|exists:surat_pengajuan,id_surat_pengajuan',
            'barang_dipilih' => 'required|array|min:1',
            'barang_dipilih.*' => 'exists:daftar_barang,id_daftar_barang',
            'barang' => 'required|array',
        ]);

        try {
            DB::beginTransaction();

            $woId = $request->id_surat_pengajuan;
            $barangDipilih = $request->barang_dipilih;
            $barangData = $request->barang;

            $workOrder = SuratPengajuan::findOrFail($woId);
            $permintaan = PermintaanBarang::where('id_surat_pengajuan', $woId)->first();

            // Array untuk menyimpan format string 'unit' baru
            $unitParts = [];
            $totalHargaBaru = 0;

            // Hapus log pembelian lama (jika ada) untuk WO ini
            DaftarPembelianBarang::where('id_surat_pengajuan', $woId)->delete();
            
            // Jika ada permintaan, hapus detail barang lama dan siapkan untuk update
            if ($permintaan) {
                DetailBarangPermintaan::where('id_permintaan_barang', $permintaan->id_permintaan_barang)->delete();
            }

            foreach ($barangDipilih as $barangId) {
                if (!isset($barangData[$barangId])) {
                    continue;
                }

                $data = $barangData[$barangId];
                $namaBarang = $data['nama'] ?? '';
                $jumlah = (int)($data['jumlah'] ?? 1);
                $satuan = $data['satuan'] ?? '';
                $hargaSatuan = (float)($data['harga_satuan'] ?? 0);
                $totalHarga = $hargaSatuan * $jumlah;

                // Format untuk field 'unit': "Nama Barang (qty: X)"
                $unitParts[] = $namaBarang . ' (qty: ' . $jumlah . ')';

                // Simpan ke log daftar_pembelian_barang
                DaftarPembelianBarang::create([
                    'id_barang' => $barangId,
                    'id_surat_pengajuan' => $woId,
                    'jumlah' => $jumlah,
                    'harga_satuan' => $hargaSatuan,
                    'total_harga' => $totalHarga,
                ]);

                $totalHargaBaru += $totalHarga;

                // Update/create detail_barang_permintaan jika ada permintaan
                if ($permintaan) {
                    DetailBarangPermintaan::create([
                        'id_permintaan_barang' => $permintaan->id_permintaan_barang,
                        'id_daftar_barang_master' => $barangId,
                        'nama_barang' => $namaBarang,
                        'jumlah' => $jumlah,
                        'satuan' => $satuan,
                        'estimasi_harga' => $totalHarga,
                    ]);
                }
            }

            // Update field 'unit' di surat_pengajuan dengan barang yang dipilih saja
            $newUnitString = implode(', ', $unitParts);
            $workOrder->update([
                'unit' => $newUnitString,
                'updated_at' => now(),
            ]);

            // Update total_estimasi_harga di permintaan_barang jika ada
            if ($permintaan) {
                $permintaan->update([
                    'total_estimasi_harga' => $totalHargaBaru,
                    'id_purchasing' => Session::get('user_id'),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Daftar barang pembelian berhasil disimpan. Total: ' . count($barangDipilih) . ' barang.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menyimpan daftar barang: ' . $e->getMessage());
        }
    }
}
