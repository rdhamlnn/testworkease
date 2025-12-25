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
        
        // Prioritas: Jika status mengandung "Ditolak Atasan", gunakan kolom status langsung
        // karena saat ditolak oleh Atasan, id_verifikator direset ke 1 (Menunggu)
        $statusValue = $workOrder->status;
        if ($statusValue && strpos($statusValue, 'Ditolak Atasan') !== false) {
            $status = $statusValue;
        } else {
            $status = $workOrder->verifikator ? $workOrder->verifikator->nama_status : ($workOrder->status ?? 'Menunggu');
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
            'dokumentasi_url' => $workOrder->dokumentasi ? asset('storage/' . $workOrder->dokumentasi) : null,
            'status' => $status,
            'id_verifikator' => $workOrder->id_verifikator,
            'catatan_penolakan' => $workOrder->catatan_penolakan,
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
     * Resend work order that was rejected.
     * Changes status from Ditolak (3) back to Menunggu (1) so it can be resubmitted.
     */
    public function resendWorkOrder($id)
    {
        try {
            $workOrder = SuratPengajuan::findOrFail($id);
            $userDivisiNama = DB::table('divisi')->where('id_divisi', Session::get('user_divisi'))->value('nama_divisi');
            
            // Validasi: Hanya divisi pengaju yang bisa resend
            // Gunakan case-insensitive comparison untuk menghindari masalah case
            if (!$userDivisiNama || strtolower(trim($workOrder->divisi_pengaju)) !== strtolower(trim($userDivisiNama))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk mengirim ulang work order ini.'
                ], 403);
            }
            
            // Validasi: Hanya work order yang ditolak yang bisa di-resend
            // Check both id_verifikator == 3 OR status contains 'Ditolak' (handles 'Ditolak Atasan' case)
            $isDitolak = $workOrder->id_verifikator == 3 || 
                         ($workOrder->status && strpos($workOrder->status, 'Ditolak') !== false);
            
            if (!$isDitolak) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya work order yang ditolak yang dapat dikirim ulang.'
                ], 400);
            }
            
            // Validasi: Hanya jenis work order "Pembelian" yang bisa dikirim ulang
            $workOrder->load('jenisWorkOrder');
            $jenisWo = $workOrder->jenisWorkOrder ? strtolower($workOrder->jenisWorkOrder->nama_jenis_wo) : '';
            
            if ($jenisWo !== 'pembelian') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya work order dengan jenis "Pembelian" yang dapat dikirim ulang.'
                ], 400);
            }
            
            // Update status dari Ditolak ke Menunggu
            // Tidak mengubah 'ditujukan' - tetap kirim ke divisi tujuan yang asli
            $workOrder->update([
                'id_verifikator' => 1, // 1 = Menunggu
                'status' => 'Menunggu'
            ]);
            
            Session::flash('success', 'Work Order berhasil dikirim ulang!');
            Session::flash('from_crud', true);
            
            // Deteksi route berdasarkan divisi
            $redirectRoute = 'kadivqc.work-order'; // Default
            $divisiLower = strtolower($userDivisiNama);
            
            if (strpos($divisiLower, 'mekanik') !== false) {
                $redirectRoute = 'kadivmekanik.work-order';
            } elseif (strpos($divisiLower, 'plasma') !== false) {
                $redirectRoute = 'kadivplasma.work-order';
            } elseif (strpos($divisiLower, 'produksi') !== false) {
                $redirectRoute = 'kadivproduksi.work-order';
            } elseif (strpos($divisiLower, 'quality') !== false || strpos($divisiLower, 'qc') !== false) {
                $redirectRoute = 'kadivqc.work-order';
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Work Order berhasil dikirim ulang ke divisi tujuan!',
                'redirect' => route($redirectRoute, ['from' => 'crud'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim ulang work order: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Cek stok barang for work order.
     */
    public function cekStokBarang($id)
    {
        try {
            $workOrder = SuratPengajuan::with('jenisWorkOrder')->findOrFail($id);
            $permintaanBarang = PermintaanBarang::where('id_surat_pengajuan', $id)->first();
            
            $barangList = [];
            
            // Cek apakah ada data di PermintaanBarang
            if ($permintaanBarang) {
                $detailBarang = DetailBarangPermintaan::where('id_permintaan_barang', $permintaanBarang->id_permintaan_barang)
                    ->with('masterBarang')
                    ->get();
                
                foreach ($detailBarang as $detail) {
                    $barangList[] = [
                        'nama_barang' => $detail->nama_barang,
                        'jumlah' => $detail->jumlah,
                        'satuan' => $detail->satuan ?? '-',
                        'id_master' => $detail->id_daftar_barang_master
                    ];
                }
            }
            
            // Jika tidak ada data di PermintaanBarang, coba parse dari field unit (untuk jenis Pembelian/Permintaan)
            if (empty($barangList) && $workOrder->unit && $workOrder->unit !== '-') {
                $jenisWo = $workOrder->jenisWorkOrder ? strtolower($workOrder->jenisWorkOrder->nama_jenis_wo) : '';
                
                // Parse jika jenis WO adalah Pembelian atau Permintaan
                if ($jenisWo === 'pembelian' || $jenisWo === 'permintaan') {
                    $unitData = $workOrder->unit;
                    
                    if (is_string($unitData)) {
                        // Parse format: "Barang1 (qty: 5), Barang2 (qty: 10)"
                        if (strpos($unitData, ',') !== false) {
                            $parts = explode(',', $unitData);
                        } else {
                            $parts = [$unitData];
                        }
                        
                        foreach ($parts as $part) {
                            $part = trim($part);
                            if (empty($part)) continue;
                            
                            $qtyMatch = [];
                            preg_match('/\(qty:\s*(\d+)\)/i', $part, $qtyMatch);
                            
                            if (!empty($qtyMatch)) {
                                $qty = (int)$qtyMatch[1];
                                $namaBarang = trim(preg_replace('/\s*\(qty:\s*\d+\)/i', '', $part));
                            } else {
                                $qty = 1;
                                $namaBarang = $part;
                            }
                            
                            if (!empty($namaBarang)) {
                                $barangList[] = [
                                    'nama_barang' => $namaBarang,
                                    'jumlah' => $qty,
                                    'satuan' => '-',
                                    'id_master' => null
                                ];
                            }
                        }
                    }
                }
            }
            
            // Jika tidak ada barang ditemukan
            if (empty($barangList)) {
                return response()->json([
                    'success' => true,
                    'has_barang' => false,
                    'message' => 'Work Order ini tidak memiliki daftar barang',
                    'stok_info' => []
                ]);
            }
            
            // Cek stok untuk setiap barang
            $stokInfo = [];
            $allStokCukup = true;
            $adaBarangHabis = false;
            
            foreach ($barangList as $barang) {
                // Cari master barang berdasarkan id atau nama
                $masterBarang = null;
                if (!empty($barang['id_master'])) {
                    $masterBarang = DaftarBarang::find($barang['id_master']);
                }
                if (!$masterBarang) {
                    $masterBarang = DaftarBarang::where('nama_barang', $barang['nama_barang'])->first();
                }
                
                $stokTersedia = $masterBarang ? ($masterBarang->stok ?? 0) : 0;
                $stokCukup = $stokTersedia >= $barang['jumlah'];
                $statusStok = $stokTersedia == 0 ? 'habis' : ($stokTersedia < $barang['jumlah'] ? 'kurang' : 'cukup');
                
                if ($statusStok !== 'cukup') $allStokCukup = false;
                if ($statusStok === 'habis') $adaBarangHabis = true;
                
                // Ambil satuan dari master barang jika tersedia, fallback ke data barang
                $satuan = '-';
                if ($masterBarang && !empty($masterBarang->satuan)) {
                    $satuan = $masterBarang->satuan;
                } elseif (!empty($barang['satuan']) && $barang['satuan'] !== '-') {
                    $satuan = $barang['satuan'];
                }
                
                $stokInfo[] = [
                    'id_barang' => $masterBarang ? $masterBarang->id_daftar_barang : null,
                    'nama_barang' => $masterBarang ? $masterBarang->nama_barang : $barang['nama_barang'],
                    'jumlah_diminta' => $barang['jumlah'],
                    'satuan' => $satuan,
                    'stok_tersedia' => $stokTersedia,
                    'status_stok' => $statusStok,
                    'stok_cukup' => $stokCukup,
                    'kekurangan' => max(0, $barang['jumlah'] - $stokTersedia)
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
