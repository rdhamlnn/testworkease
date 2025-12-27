<?php

namespace App\Http\Controllers;

use App\Traits\StatusHelper;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use App\Models\PermintaanBarang;
use App\Models\StatusWo;
use App\Models\SuratPengajuan;
use App\Models\Akun;
use App\Models\DetailBarangPermintaan;
use App\Models\DaftarBarang;
use App\Models\DaftarPembelianBarang;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AtasanController extends Controller
{
    use \App\Traits\StatusHelper, \App\Traits\UserProfileActions, \App\Traits\WorkOrderActions;

    /**
     * Get the view name for profile.
     */
    protected function getProfileView()
    {
        return 'atasan.profile';
    }

    /**
     * Get redirect route for work order masukan.
     */
    protected function getDaftarPengajuanRedirectRoute()
    {
        return route('atasan.work-order-masuk', ['from' => 'crud']);
    }

    /**
     * Display the dashboard page.
     */
    public function dashboard()
    {
        // Ambil id status dari tabel status_wo
        $statusMenungguApprovalId = $this->getStatusId('Menunggu Approval Atasan');
        $statusDisetujuiId = $this->getStatusId('Disetujui Atasan');
        $statusDitolakId = $this->getStatusId('Ditolak Atasan');

        // Statistik
        $totalWorkOrder = SuratPengajuan::where('ditujukan', 'Atasan')->count();
        $menungguApproval = PermintaanBarang::forStatus($statusMenungguApprovalId)->count();
        $disetujui = PermintaanBarang::forStatus($statusDisetujuiId)->count();
        $ditolak = PermintaanBarang::forStatus($statusDitolakId)->count();
        
        // Chart data
        $monthlyApprovalTrend = PermintaanBarang::select(
                DB::raw('MONTH(tanggal_permintaan) as month'),
                DB::raw('YEAR(tanggal_permintaan) as year'),
                DB::raw('count(*) as total')
            )
            ->whereIn('id_status_wo', [$statusDisetujuiId, $statusDitolakId])
            ->where('tanggal_permintaan', '>=', DB::raw('DATE_SUB(NOW(), INTERVAL 6 MONTH)'))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();
        
        $approvalPercentage = ['Disetujui' => $disetujui, 'Ditolak' => $ditolak];
        
        // Recent activities
        $recentActivities = PermintaanBarang::with(['suratPengajuan', 'akun.karyawan', 'statusWo'])
            ->whereIn('id_status_wo', [$statusDisetujuiId, $statusDitolakId])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('atasan.dashboard', compact(
            'totalWorkOrder', 'menungguApproval', 'disetujui', 'ditolak',
            'monthlyApprovalTrend', 'approvalPercentage', 'recentActivities'
        ));
    }
    
    /**
     * @deprecated View file has been removed. This route is no longer available.
     */
    public function approvalPermintaan()
    {
        abort(404, 'Halaman Approval Permintaan sudah tidak tersedia.');
    }
    
    /**
     * @deprecated View file has been removed. This route is no longer available.
     */
    public function riwayatApproval()
    {
        abort(404, 'Halaman Riwayat Approval sudah tidak tersedia.');
    }

    
    /**
     * Approve permintaan barang.
     * Mengembalikan JSON jika request AJAX, atau redirect biasa jika form normal.
     */
    public function approvePermintaan(Request $request, $id)
    {
        try {
            $permintaan = PermintaanBarang::findOrFail($id);
            $statusDisetujuiId = StatusWo::where('nama_status', 'Disetujui Atasan')->value('id_status_wo');

            $permintaan->update([
                'status' => 'Disetujui Atasan',
                'id_status_wo' => $statusDisetujuiId,
                'id_atasan' => Session::get('user_id'),
                'updated_at' => now(),
            ]);

            // Jika request AJAX / expects JSON, kembalikan response JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Permintaan berhasil disetujui!',
                    'redirect' => route('atasan.approval-permintaan')
                ]);
            }

            // Kalau bukan AJAX, redirect kembali ke halaman approval dengan flash message
            return redirect()->route('atasan.approval-permintaan', ['from' => 'crud'])
                ->with('success', 'Permintaan berhasil disetujui!');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyetujui permintaan: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Gagal menyetujui permintaan: ' . $e->getMessage());
        }
    }
    
    /**
     * Reject permintaan barang.
     */
    public function rejectPermintaan(Request $request, $id)
    {
        try {
            $permintaan = PermintaanBarang::findOrFail($id);
            $statusDitolakId = StatusWo::where('nama_status', 'Ditolak Atasan')->value('id_status_wo');

            $permintaan->update([
                'status' => 'Ditolak Atasan',
                'id_status_wo' => $statusDitolakId,
                'id_atasan' => Session::get('user_id'),
                'catatan_atasan' => $request->catatan_atasan ?? null,
                'updated_at' => now(),
            ]);
            
            return redirect()->route('atasan.approval-permintaan', ['from' => 'crud'])->with('success', 'Permintaan berhasil ditolak!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menolak permintaan: ' . $e->getMessage());
        }
    }
    
    /**
     * Display work order masuk page (WO yang diterima dari Purchasing).
     */
    public function workOrderMasuk()
    {
        $userDivisiNama = DB::table('divisi')->where('id_divisi', Session::get('user_divisi'))->value('nama_divisi') ?? 'Atasan';
        
        $workOrders = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder', 'parent', 'permintaanBarang.daftarBarang.masterBarang', 'daftarPembelianBarang.barang'])
            ->toDivisi($userDivisiNama)
            ->where('divisi_pengaju', '!=', $userDivisiNama)
            ->byVerifikator(1)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Preload semua harga barang untuk efisiensi fallback
        $masterBarangPrices = DB::table('daftar_barang')
            ->whereNotNull('harga_barang')
            ->where('harga_barang', '>', 0)
            ->pluck('harga_barang', 'nama_barang')
            ->toArray();
        
        // Hitung total harga untuk setiap work order
        $workOrders->each(function ($wo) use ($masterBarangPrices) {
            $totalHarga = 0;
            $wo->realization_available = false; // Flag untuk view
            
            // Prioritas 0: Cek Log Realisasi Pembelian (DaftarPembelianBarang)
            // Ini yang paling akurat jika Purchasing sudah input harga
            if ($wo->daftarPembelianBarang && $wo->daftarPembelianBarang->count() > 0) {
                 $wo->realization_available = true;
                 $wo->realization_available = true;
                 $tempItems = []; // Use temporary array
                 
                 foreach($wo->daftarPembelianBarang as $log) {
                     $totalHarga += $log->total_harga;
                     
                     $tempItems[] = [
                         'nama_barang' => $log->barang ? $log->barang->nama_barang : 'Unknown',
                         'jumlah' => $log->jumlah,
                         'satuan' => $log->barang ? $log->barang->satuan : '-',
                         'harga' => $log->harga_satuan
                     ];
                 }
                 $wo->realization_items = $tempItems; // Assign once at the end
            } else {
                // ... Existing Logic for Fallback ...
                
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
            }
            
            $wo->calculated_total_harga = $totalHarga;
        });
        
        return view('atasan.work_order_masuk', compact('workOrders'));
    }

    public function riwayatWorkOrder()
    {
        $userDivisiNama = DB::table('divisi')->where('id_divisi', Session::get('user_divisi'))->value('nama_divisi') ?? 'Atasan';
        
        $workOrders = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder', 'parent', 'permintaanBarang.daftarBarang.masterBarang', 'daftarPembelianBarang.barang'])
            ->dibuatAtauDiterima($userDivisiNama)
            ->byVerifikator([2, 3])
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Preload semua harga barang untuk efisiensi fallback
        $masterBarangPrices = DB::table('daftar_barang')
            ->whereNotNull('harga_barang')
            ->where('harga_barang', '>', 0)
            ->pluck('harga_barang', 'nama_barang')
            ->toArray();
            
        // Hitung total harga untuk setiap work order (Copy of logic from workOrderMasuk)
        $workOrders->each(function ($wo) use ($masterBarangPrices) {
            $totalHarga = 0;
            $wo->realization_available = false; // Flag untuk view
            
            // Prioritas 0: Cek Log Realisasi Pembelian (DaftarPembelianBarang)
            if ($wo->daftarPembelianBarang && $wo->daftarPembelianBarang->count() > 0) {
                 $wo->realization_available = true;
                 $tempItems = []; 
                 
                 foreach($wo->daftarPembelianBarang as $log) {
                     $totalHarga += $log->total_harga;
                     
                     $tempItems[] = [
                         'nama_barang' => $log->barang ? $log->barang->nama_barang : 'Unknown',
                         'jumlah' => $log->jumlah,
                         'satuan' => $log->barang ? $log->barang->satuan : '-',
                         'harga' => $log->harga_satuan
                     ];
                 }
                 $wo->realization_items = $tempItems;
            } else {
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
                
                // Prioritas 3: Parse dari field unit if still 0
                if ($totalHarga == 0 && $wo->unit) {
                    $unitString = is_object($wo->unit) ? ($wo->unit->nama_unit ?? '') : $wo->unit;
                    preg_match_all('/([^,]+?)(?:\s*\(qty:\s*(\d+)\))?(?:,|$)/i', $unitString, $matches, PREG_SET_ORDER);
                    
                    foreach ($matches as $match) {
                        $namaBarang = trim($match[1]);
                        $qty = isset($match[2]) ? (int)$match[2] : 1;
                        if (empty($namaBarang)) continue;
                        
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
            }
            
            $wo->calculated_total_harga = $totalHarga;
        });
        
        $daftarBarang = DaftarBarang::orderBy('nama_barang')->get();
        
        return view('atasan.riwayat_work_order', compact('workOrders', 'daftarBarang'));
    }




    /**
     * Cetak work order PDF.
     */
    public function cetakpdf($id)
    {
        $wo = SuratPengajuan::with([
            'jenisWorkOrder',
            'verifikator',
            'akun.karyawan',
            'akun.divisi',
            'divisiPengaju',
            'unit',
            'parent',
            'permintaanBarang.daftarBarang'
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

    /**
     * Approve work order dari Atasan.
     * Work order yang disetujui akan menambah status permintaan barang ke 'Disetujui Atasan'
     * sehingga akan tampil di halaman Beli Barang Purchasing.
     */
    public function approveWorkOrder($id)
    {
        try {
            $workOrder = SuratPengajuan::with('permintaanBarang')->findOrFail($id);
            $userDivisiNama = DB::table('divisi')->where('id_divisi', Session::get('user_divisi'))->value('nama_divisi');
            
            // Validasi akses
            if ($workOrder->ditujukan !== $userDivisiNama) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menyetujui work order ini.'
                ], 403);
            }
            
            // Update status work order
            $workOrder->update([
                'id_verifikator' => 2, // 2 = Disetujui
                'status' => 'Disetujui Atasan'
            ]);
            
            $statusDisetujuiAtasanId = StatusWo::where('nama_status', 'Disetujui Atasan')->value('id_status_wo');

            // Update status permintaan barang jika ada
            if ($workOrder->permintaanBarang) {
                $workOrder->permintaanBarang->update([
                    'status' => 'Disetujui Atasan',
                    'id_status_wo' => $statusDisetujuiAtasanId,
                    'id_atasan' => Session::get('user_id'),
                ]);
            } else {
                // Jika permintaan barang belum ada (misal dari Purchasing murni),
                // Cek apakah ada logs realisasi pembelian
                $logs = DaftarPembelianBarang::with('barang')->where('id_surat_pengajuan', $id)->get();
                
                if ($logs->count() > 0) {
                    $totalHarga = $logs->sum('total_harga');
                    
                    // Buat PermintaanBarang baru
                    $permintaan = PermintaanBarang::create([
                        'no_permintaan_barang' => $this->generateNoPermintaan('LOG'),
                        'id_surat_pengajuan' => $id,
                        'tanggal_permintaan' => now(),
                        'status' => 'Disetujui Atasan',
                        'id_status_wo' => $statusDisetujuiAtasanId,
                        'total_estimasi_harga' => $totalHarga,
                        'id_akun' => $workOrder->id_akun, // Attribution to WO creator
                        'id_atasan' => Session::get('user_id'),
                    ]);
                    
                    // Buat DetailBarangPermintaan dari Logs
                    foreach ($logs as $log) {
                        DetailBarangPermintaan::create([
                            'id_permintaan_barang' => $permintaan->id_permintaan_barang,
                            'id_daftar_barang_master' => $log->id_barang,
                            'nama_barang' => $log->barang ? $log->barang->nama_barang : 'Unknown',
                            'jumlah' => $log->jumlah,
                            'satuan' => $log->barang ? $log->barang->satuan : '-',
                            'estimasi_harga' => $log->total_harga,
                        ]);
                    }
                }
            }
            
            Session::flash('success', 'Work Order berhasil disetujui! Data akan muncul di halaman Beli Barang.');
            return response()->json([
                'success' => true,
                'message' => 'Work Order berhasil disetujui! Data akan muncul di halaman Beli Barang.',
                'redirect' => route('atasan.work-order-masuk', ['from' => 'crud'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyetujui work order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject work order dari Atasan.
     * Work order yang ditolak akan dikembalikan ke Purchasing untuk dikirim ulang.
     */
    public function rejectWorkOrder(Request $request, $id)
    {
        try {
            $workOrder = SuratPengajuan::with('permintaanBarang')->findOrFail($id);
            $userDivisiNama = DB::table('divisi')->where('id_divisi', Session::get('user_divisi'))->value('nama_divisi');
            
            // Validasi akses
            if ($workOrder->ditujukan !== $userDivisiNama) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk menolak work order ini.'
                ], 403);
            }
            
            // Kembalikan ke Purchasing untuk dikirim ulang
            $workOrder->update([
                'ditujukan' => 'Purchasing', // Kembalikan ke Purchasing
                'id_verifikator' => 1, // Reset ke menunggu
                'status' => 'Ditolak Atasan - Perlu Dikirim Ulang',
                'catatan_penolakan' => $request->catatan_penolakan ?? null,
            ]);
            
            // Update status permintaan barang jika ada
            if ($workOrder->permintaanBarang) {
                $statusDitolakId = StatusWo::where('nama_status', 'Ditolak Atasan')->value('id_status_wo');
                
                $workOrder->permintaanBarang->update([
                    'status' => 'Ditolak Atasan',
                    'id_status_wo' => $statusDitolakId,
                    'id_atasan' => Session::get('user_id'),
                    'catatan_atasan' => $request->catatan_penolakan ?? null,
                ]);
            }
            
            Session::flash('success', 'Work Order ditolak dan dikembalikan ke Purchasing untuk dikirim ulang.');
            return response()->json([
                'success' => true,
                'message' => 'Work Order ditolak dan dikembalikan ke Purchasing untuk dikirim ulang.',
                'redirect' => route('atasan.work-order-masuk', ['from' => 'crud'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak work order: ' . $e->getMessage()
            ], 500);
        }
    }
}
