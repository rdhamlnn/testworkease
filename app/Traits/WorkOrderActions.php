<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\SuratPengajuan;
use App\Models\Unit;
use App\Models\PermintaanBarang;
use App\Models\DetailBarangPermintaan;
use App\Models\DaftarBarang;
use Carbon\Carbon;

trait WorkOrderActions
{
    use FileHandler;

    /**
     * Show work order.
     */
    public function showWorkOrder($id)
    {
        $workOrder = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder'])->findOrFail($id);
        
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
            'dokumentasi_url' => $workOrder->dokumentasi ? asset('storage/' . $workOrder->dokumentasi) : null,
            'status' => $workOrder->verifikator ? $workOrder->verifikator->nama_status : ($workOrder->status ?? 'Menunggu'),
            'id_verifikator' => $workOrder->id_verifikator,
        ];

        return response()->json($data);
    }

    /**
     * Delete work order.
     */
    public function hapusWorkOrder($id)
    {
        try {
            $workOrder = SuratPengajuan::findOrFail($id);
            
            if (in_array($workOrder->id_verifikator, [2, 3])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Work Order tidak dapat dihapus karena sudah disetujui atau ditolak.'
                ], 403);
            }

            $this->deleteFile($workOrder->dokumentasi);
            $workOrder->delete();

            session()->flash('success', 'Data berhasil dihapus!');
            session()->flash('from_crud', true);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus!',
                'redirect' => $this->getWorkOrderRedirectRoute()
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
            $userDivisiNama = DB::table('divisi')->where('id_divisi', Session::get('user_divisi'))->value('nama_divisi');
            
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
            
            Session::flash('success', 'Work Order berhasil disetujui!');
            return response()->json([
                'success' => true,
                'message' => 'Work Order berhasil disetujui!',
                'redirect' => $this->getDaftarPengajuanRedirectRoute()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui work order: ' . $e->getMessage()
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
            $userDivisiNama = DB::table('divisi')->where('id_divisi', Session::get('user_divisi'))->value('nama_divisi');
            
            if ($workOrder->ditujukan !== $userDivisiNama) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menolak work order ini.'
                ], 403);
            }
            
            $workOrder->update([
                'id_verifikator' => 3, // 3 = Ditolak
                'status' => 'Ditolak'
            ]);
            
            Session::flash('success', 'Work Order berhasil ditolak!');
            return response()->json([
                'success' => true,
                'message' => 'Work Order berhasil ditolak!',
                'redirect' => $this->getDaftarPengajuanRedirectRoute()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak work order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cek stok barang for work order.
     */
    public function cekStokBarang($id)
    {
        try {
            $permintaanBarang = PermintaanBarang::where('id_surat_pengajuan', $id)->first();
            
            if (!$permintaanBarang) {
                return response()->json([
                    'success' => true,
                    'has_barang' => false,
                    'message' => 'Work Order ini tidak memiliki daftar barang',
                    'stok_info' => []
                ]);
            }
            
            $detailBarang = DetailBarangPermintaan::where('id_permintaan_barang', $permintaanBarang->id_permintaan_barang)
                ->with('masterBarang')
                ->get();
            
            $stokInfo = [];
            $allStokCukup = true;
            $adaBarangHabis = false;
            
            foreach ($detailBarang as $detail) {
                $masterBarang = $detail->id_daftar_barang_master ? DaftarBarang::find($detail->id_daftar_barang_master) : DaftarBarang::where('nama_barang', $detail->nama_barang)->first();
                $stokTersedia = $masterBarang ? ($masterBarang->stok ?? 0) : 0;
                $stokCukup = $stokTersedia >= $detail->jumlah;
                $statusStok = $stokTersedia == 0 ? 'habis' : ($stokTersedia < $detail->jumlah ? 'kurang' : 'cukup');
                
                if ($statusStok !== 'cukup') $allStokCukup = false;
                if ($statusStok === 'habis') $adaBarangHabis = true;
                
                $stokInfo[] = [
                    'nama_barang' => $detail->nama_barang,
                    'jumlah_diminta' => $detail->jumlah,
                    'satuan' => $detail->satuan ?? '-',
                    'stok_tersedia' => $stokTersedia,
                    'status_stok' => $statusStok,
                    'stok_cukup' => $stokCukup,
                    'kekurangan' => max(0, $detail->jumlah - $stokTersedia)
                ];
            }
            
            return response()->json([
                'success' => true,
                'has_barang' => true,
                'all_stok_cukup' => $allStokCukup,
                'ada_barang_habis' => $adaBarangHabis,
                'stok_info' => $stokInfo,
                'message' => $allStokCukup ? 'Semua stok barang tersedia' : ($adaBarangHabis ? 'Ada barang yang habis' : 'Ada barang yang stoknya kurang')
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mengecek stok: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate common work order number.
     */
    protected function generateWorkOrderNumber($prefix = 'KCE')
    {
        $year = Carbon::now('Asia/Makassar')->year;
        $month = Carbon::now('Asia/Makassar')->month;
        
        $lastWorkOrder = SuratPengajuan::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('created_at', 'desc')
            ->first();
        
        $sequence = '01';
        if ($lastWorkOrder) {
            $lastNumber = explode('/', $lastWorkOrder->no_surat_pengajuan)[0];
            $sequence = str_pad((int)$lastNumber + 1, 2, '0', STR_PAD_LEFT);
        }
        
        return "{$sequence}/{$prefix}/KCE/{$year}";
    }

    /**
     * Generate common permintaan number.
     */
    protected function generateNoPermintaan($prefix = 'LOG')
    {
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;
        
        $lastPermintaan = PermintaanBarang::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('created_at', 'desc')
            ->first();
        
        $sequence = '001';
        if ($lastPermintaan) {
            $lastNumber = explode('/', $lastPermintaan->no_permintaan_barang)[0];
            $sequence = str_pad((int)$lastNumber + 1, 3, '0', STR_PAD_LEFT);
        }
        
        return "{$sequence}/{$prefix}/KCE/{$year}";
    }

    /**
     * Helper to get redirect routes. To be customized in controller if needed.
     */
    protected function getWorkOrderRedirectRoute() { return route(str_replace('Controller', '', class_basename($this)) . '.work-order'); }
    protected function getDaftarPengajuanRedirectRoute() { return route(str_replace('Controller', '', class_basename($this)) . '.daftar-pengajuan-work-order', ['from' => 'crud']); }
}
