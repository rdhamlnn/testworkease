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
    use \App\Traits\WorkOrderActions, \App\Traits\UserProfileActions;

    /**
     * Get the view name for profile.
     */
    protected function getProfileView()
    {
        return 'kadivqc.profile';
    }

    /**
     * Display the dashboard page.
     */
    public function dashboard()
    {
        $userId = Session::get('user_id');
        $userDivisi = Session::get('user_divisi');
        $userDivisiNama = DB::table('divisi')->where('id_divisi', $userDivisi)->value('nama_divisi') ?? 'Quality Control';
        
        // Statistik - WO yang dibuat oleh divisi pengaju
        $totalWODibuat = SuratPengajuan::where('id_akun', $userId)->count();
        
        // Statistik - WO yang diterima oleh divisi Quality Control
        $totalWODiterima = SuratPengajuan::toDivisi($userDivisiNama)->count();
        
        $woPending = SuratPengajuan::where('id_akun', $userId)->where('status', 'Menunggu')->count();
        $woSelesai = SuratPengajuan::where('id_akun', $userId)->where('status', 'Selesai')->count();
        
        // Trend data
        $monthlyWOTrend = SuratPengajuan::select(
                DB::raw('MONTH(tanggal) as month'),
                DB::raw('YEAR(tanggal) as year'),
                DB::raw('count(*) as total')
            )
            ->where('id_akun', $userId)
            ->where('tanggal', '>=', DB::raw('DATE_SUB(NOW(), INTERVAL 6 MONTH)'))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();
        
        $woStatus = SuratPengajuan::select('status', DB::raw('count(*) as total'))
            ->where('id_akun', $userId)
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status')
            ->toArray();
        
        // Recent activities
        $recentActivities = SuratPengajuan::where('id_akun', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('kadivqc.dashboard', compact(
            'totalWODibuat', 'totalWODiterima', 'woPending', 'woSelesai',
            'monthlyWOTrend', 'woStatus', 'recentActivities'
        ));
    }
    
    /**
     * Display work order page.
     */
    public function workOrder()
    {
        $userDivisiNama = DB::table('divisi')->where('id_divisi', Session::get('user_divisi'))->value('nama_divisi') ?? 'Quality Control';
        
        $workOrders = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder'])
            ->fromDivisi($userDivisiNama)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Variables compatible with purchasing view
        $divisiTujuan = Divisi::where('nama_divisi', '!=', 'Administrator')->orderBy('nama_divisi')->get();
        $unit = Unit::orderBy('nama_unit')->get();
        $jenisWorkOrder = \App\Models\JenisWorkOrder::all();
        $nextWorkOrderNumber = $this->generateWorkOrderNumber('QC');
        $daftarBarang = \App\Models\DaftarBarang::orderBy('nama_barang')->get();
        $divisiPengaju = $userDivisiNama;

        return view('kadivqc.work_order', compact('userDivisiNama', 'divisiTujuan', 'unit', 'jenisWorkOrder', 'nextWorkOrderNumber', 'workOrders', 'daftarBarang', 'divisiPengaju'));
    }
    
    /**
     * Display daftar work order page.
     */
    public function daftarWorkOrder()
    {
        $userDivisiNama = DB::table('divisi')->where('id_divisi', Session::get('user_divisi'))->value('nama_divisi') ?? 'Quality Control';
        
        $workOrders = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder'])
            ->toDivisi($userDivisiNama)
            ->where('divisi_pengaju', '!=', $userDivisiNama)
            ->byVerifikator(1)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('kadivqc.daftar_work_order', compact('workOrders'));
    }
    
    /**
     * Display riwayat work order page.
     */
    public function riwayatWorkOrder()
    {
        $userDivisiNama = DB::table('divisi')->where('id_divisi', Session::get('user_divisi'))->value('nama_divisi') ?? 'Quality Control';
        
        $workOrders = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder'])
            ->dibuatAtauDiterima($userDivisiNama)
            ->byVerifikator([2, 3])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('kadivqc.riwayat_work_order', compact('workOrders'));
    }

    /**
     * Store work order.
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

        if ($request->ditujukan === 'Administrator') {
            return redirect()->back()->with('error', 'Tidak dapat mengajukan ke Administrator.');
        }

        try {
            $unitId = Unit::where('nama_unit', $request->unit)->value('id_unit') ?: 1;
            $userDivisiNama = DB::table('divisi')->where('id_divisi', Session::get('user_divisi'))->value('nama_divisi') ?? 'Quality Control';

            $dokumentasiPath = $request->hasFile('dokumentasi') ? $this->handleUpload($request->file('dokumentasi'), 'work-orders') : null;

            $workOrder = SuratPengajuan::create([
                'no_surat_pengajuan' => $this->generateWorkOrderNumber('QC'),
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

            $this->processPermintaanBarang($request, $workOrder);

            return redirect()->route('kadivqc.work-order', ['from' => 'crud'])->with('success', 'Data berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->route('kadivqc.work-order')->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Update work order.
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
            // Hanya cegah edit untuk work order yang sudah Disetujui (2)
            // Work order yang Ditolak (3) boleh diedit untuk diperbaiki dan dikirim ulang
            if ($workOrder->id_verifikator == 2) {
                return redirect()->back()->with('error', 'Work Order sudah disetujui dan tidak dapat diedit.');
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
                'divisi_pengaju' => $request->divisi_pengaju,
                'unit' => $unitValue,
                'uraian' => $request->uraian,
                'dokumentasi' => $dokumentasiPath,
                'id_unit' => $unitId
            ]);

            return redirect()->route('kadivqc.work-order', ['from' => 'crud'])->with('success', 'Data berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->route('kadivqc.work-order')->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Process auto-creation of PermintaanBarang.
     */
    private function processPermintaanBarang(Request $request, $workOrder)
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
}
