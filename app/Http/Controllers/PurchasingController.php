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
use Carbon\Carbon;

class PurchasingController extends Controller
{
    /**
     * Display the dashboard page.
     */
    public function dashboard()
    {
        $userId = Session::get('user_id');
        
        // Ambil id status dari master status_wo
        $statusMenungguPurchasingId = StatusWo::where('nama_status', 'Menunggu Purchasing')->value('id_status_wo');
        $statusMenungguApprovalId = StatusWo::where('nama_status', 'Menunggu Approval Atasan')->value('id_status_wo');
        $statusDibeliId = StatusWo::where('nama_status', 'Dibeli Purchasing')->value('id_status_wo');
        $statusDikirimId = StatusWo::where('nama_status', 'Dikirim Purchasing')->value('id_status_wo');

        // Statistik
        $totalPermintaan = PermintaanBarang::where('id_status_wo', $statusMenungguPurchasingId)->count();
        $menungguApproval = PermintaanBarang::where('id_status_wo', $statusMenungguApprovalId)->count();
        $dibeli = PermintaanBarang::where('id_status_wo', $statusDibeliId)->count();
        $dikirim = PermintaanBarang::where('id_status_wo', $statusDikirimId)->count();
        
        // Chart data
        $monthlyPermintaanTrend = PermintaanBarang::select(
                DB::raw('MONTH(tanggal_permintaan) as month'),
                DB::raw('YEAR(tanggal_permintaan) as year'),
                DB::raw('count(*) as total')
            )
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
        
        // Recent activities
        $recentActivities = PermintaanBarang::with(['suratPengajuan', 'akun'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('purchasing.dashboard', compact(
            'totalPermintaan',
            'menungguApproval',
            'dibeli',
            'dikirim',
            'monthlyPermintaanTrend',
            'statusPembelian',
            'recentActivities'
        ));
    }

    /**
     * Display work order masuk page.
     */
    public function workOrder()
    {
        $divisiId = Session::get('user_divisi');
        $divisiPengaju = Divisi::where('id_divisi', $divisiId)->value('nama_divisi') ?? 'Purchasing';
        $divisiTujuan = Divisi::where('id_divisi', '!=', $divisiId)->orderBy('nama_divisi')->get();
        $unit = Unit::orderBy('nama_unit')->get();
        $jenisWorkOrder = JenisWorkOrder::all();
        $nextNoWO = $this->generateWorkOrderNumber('PUR');
        $daftarBarang = DaftarBarang::orderBy('nama_barang')->get();
        $workOrders = SuratPengajuan::with(['unit', 'jenisWorkOrder', 'verifikator'])
            ->where('divisi_pengaju', $divisiPengaju)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('purchasing.work_order', compact('divisiPengaju', 'divisiTujuan', 'unit', 'jenisWorkOrder', 'nextNoWO', 'workOrders', 'daftarBarang'));
    }

    /**
     * Display daftar pengajuan work order page (WO yang diterima dari divisi lain).
     */
    public function daftarWorkOrder()
    {
        $userDivisi = Session::get('user_divisi');
        $userDivisiNama = DB::table('divisi')->where('id_divisi', $userDivisi)->value('nama_divisi') ?? 'Purchasing';
        
        // WO yang diterima oleh divisi user yang login (dari divisi lain)
        $workOrders = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder', 'parent'])
            ->where('ditujukan', $userDivisiNama)
            ->where('divisi_pengaju', '!=', $userDivisiNama)
            ->where('id_verifikator', 1) // Hanya status Menunggu
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('purchasing.daftar_work_order', compact('workOrders'));
    }

    public function riwayatWorkOrder()
    {
        $userDivisi = Session::get('user_divisi');
        $userDivisiNama = DB::table('divisi')->where('id_divisi', $userDivisi)->value('nama_divisi') ?? 'Purchasing';
        
        // WO yang dibuat dan diterima oleh divisi user yang login
        $workOrdersDibuat = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder', 'parent'])
            ->where('divisi_pengaju', $userDivisiNama)
            ->whereIn('id_verifikator', [2, 3]) // Disetujui atau Ditolak
            ->get();
        
        $workOrdersDiterima = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder', 'parent'])
            ->where('ditujukan', $userDivisiNama)
            ->where('divisi_pengaju', '!=', $userDivisiNama)
            ->whereIn('id_verifikator', [2, 3]) // Disetujui atau Ditolak
            ->get();
        
        $workOrders = $workOrdersDibuat->merge($workOrdersDiterima)->sortByDesc('created_at');
        
        return view('purchasing.riwayat_work_order', compact('workOrders'));
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
            $divisiNama = DB::table('divisi')->where('id_divisi', $divisiId)->value('nama_divisi') ?? 'Purchasing';

            $dokumentasiPath = null;
            if ($request->hasFile('dokumentasi')) {
                $dokumentasiPath = $request->file('dokumentasi')->store('work-orders', 'public');
            }

            $workOrder = SuratPengajuan::create([
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

            return redirect()->route('purchasing.work-order', ['from' => 'crud'])->with('success', 'Data berhasil ditambahkan');
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
     * Update work order Purchasing.
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
        $divisiNama = DB::table('divisi')->where('id_divisi', $divisiId)->value('nama_divisi') ?? 'Purchasing';

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
            return redirect()->route('purchasing.work-order', ['from' => 'crud'])
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

            return redirect()->route('purchasing.work-order', ['from' => 'crud'])->with('success', 'Work Order berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui Work Order: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Hapus work order Purchasing.
     */
    public function destroyWorkOrder($id)
    {
        $divisiId = Session::get('user_divisi');
        $divisiNama = Divisi::where('id_divisi', $divisiId)->value('nama_divisi') ?? 'Purchasing';

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
                    'redirect' => route('purchasing.work-order', ['from' => 'crud'])
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
                'redirect' => route('purchasing.work-order')
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
        $statusMenungguPurchasingId = StatusWo::where('nama_status', 'Menunggu Purchasing')->value('id_status_wo');

        $permintaanBarang = PermintaanBarang::with(['suratPengajuan', 'akun', 'statusWo', 'daftarBarang'])
            ->where('id_status_wo', $statusMenungguPurchasingId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('purchasing.permintaan_barang', compact('permintaanBarang'));
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
     * Show work order detail (API).
     */
    public function showWorkOrder($id)
    {
        $workOrder = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder', 'parent'])->findOrFail($id);

        return response()->json([
            'id_surat_pengajuan' => $workOrder->id_surat_pengajuan,
            'no_surat_pengajuan' => $workOrder->no_surat_pengajuan,
            'no_work_order' => $workOrder->no_surat_pengajuan,
            'no_wo_parent' => $workOrder->parent ? $workOrder->parent->no_surat_pengajuan : null,
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
            'harga_barang' => $workOrder->harga_barang,
            'total_harga' => $workOrder->total_harga,
            'catatan_penolakan' => $workOrder->catatan_penolakan,
        ]);
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
     */
    public function editHargaPermintaan($id)
    {
        $permintaan = PermintaanBarang::with(['suratPengajuan', 'statusWo', 'daftarBarang'])->findOrFail($id);
        
        // Validasi: hanya bisa edit jika status "Menunggu Purchasing"
        $statusMenungguPurchasingId = StatusWo::where('nama_status', 'Menunggu Purchasing')->value('id_status_wo');
        
        if ($permintaan->id_status_wo != $statusMenungguPurchasingId) {
            return redirect()->route('purchasing.permintaan-barang')
                ->with('error', 'Harga hanya dapat diupdate untuk permintaan dengan status Menunggu Purchasing.');
        }
        
        return view('purchasing.edit_harga_permintaan', compact('permintaan'));
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
            $permintaan = PermintaanBarang::findOrFail($id);
            
            // Update id_status_wo juga
            $statusDikirimId = StatusWo::where('nama_status', 'Dikirim Purchasing')->value('id_status_wo');
            
            $permintaan->update([
                'status' => 'Dikirim Purchasing',
                'id_status_wo' => $statusDikirimId,
                'updated_at' => now(),
            ]);
            
            // Simpan success message di session untuk toast notification
            Session::flash('success', 'Barang berhasil dikirim!');
            Session::flash('from_crud', true);
            
            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil dikirim!',
                'redirect' => route('purchasing.kirim-barang', ['from' => 'crud'])
            ]);
        } catch (\Exception $e) {
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
        
        return view('purchasing.profile', compact('user'));
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
            
            return redirect()->route('purchasing.profile', ['from' => 'crud'])->with('success', 'Data berhasil diperbarui');
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
            
            return redirect()->route('purchasing.profile', ['from' => 'crud'])->with('success', 'Password berhasil diubah!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengubah password: ' . $e->getMessage());
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
                    'redirect' => route('purchasing.daftar-work-order', ['from' => 'crud'])
                ], 403);
            }
            
            $workOrder->update([
                'id_verifikator' => 2, // 2 = Disetujui
                'status' => 'Disetujui'
            ]);
            
            // Set session message untuk notifikasi toast
            Session::flash('success', 'Work Order berhasil disetujui!');
            
            // Return JSON untuk AJAX dengan redirect URL yang sudah include from=crud
            $redirectUrl = route('purchasing.daftar-work-order', ['from' => 'crud']);
            
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
                'redirect' => route('purchasing.daftar-work-order', ['from' => 'crud'])
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
                    'redirect' => route('purchasing.daftar-work-order', ['from' => 'crud'])
                ], 403);
            }
            
            $workOrder->update([
                'id_verifikator' => 3, // 3 = Ditolak
                'status' => 'Ditolak'
            ]);
            
            // Set session message untuk notifikasi toast
            Session::flash('success', 'Work Order berhasil ditolak!');
            
            // Return JSON untuk AJAX dengan redirect URL yang sudah include from=crud
            $redirectUrl = route('purchasing.daftar-work-order', ['from' => 'crud']);
            
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
}
