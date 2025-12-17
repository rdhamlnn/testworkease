<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use App\Models\PermintaanBarang;
use App\Models\StatusWo;
use App\Models\SuratPengajuan;
use App\Models\Akun;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class AtasanController extends Controller
{
    /**
     * Display the dashboard page.
     */
    public function dashboard()
    {
        $userId = Session::get('user_id');
        
        // Ambil id status dari tabel status_wo
        $statusMenungguApprovalId = StatusWo::where('nama_status', 'Menunggu Approval Atasan')->value('id_status_wo');
        $statusDisetujuiId = StatusWo::where('nama_status', 'Disetujui Atasan')->value('id_status_wo');
        $statusDitolakId = StatusWo::where('nama_status', 'Ditolak Atasan')->value('id_status_wo');

        // Statistik
        $menungguApproval = PermintaanBarang::where('id_status_wo', $statusMenungguApprovalId)->count();
        $disetujui = PermintaanBarang::where('id_status_wo', $statusDisetujuiId)->count();
        $ditolak = PermintaanBarang::where('id_status_wo', $statusDitolakId)->count();
        
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
        
        $approvalPercentage = [
            'Disetujui' => $disetujui,
            'Ditolak' => $ditolak
        ];
        
        // Recent activities
        $recentActivities = PermintaanBarang::with(['suratPengajuan', 'akun', 'statusWo'])
            ->whereIn('id_status_wo', [$statusDisetujuiId, $statusDitolakId])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();
        
        return view('atasan.dashboard', compact(
            'menungguApproval',
            'disetujui',
            'ditolak',
            'monthlyApprovalTrend',
            'approvalPercentage',
            'recentActivities'
        ));
    }
    
    /**
     * Display approval permintaan page.
     */
    public function approvalPermintaan()
    {
        $statusMenungguApprovalId = StatusWo::where('nama_status', 'Menunggu Approval Atasan')->value('id_status_wo');

        $permintaanUntukApproval = PermintaanBarang::with(['suratPengajuan', 'akun', 'statusWo', 'daftarBarang'])
            ->where('id_status_wo', $statusMenungguApprovalId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('atasan.approval_permintaan', compact('permintaanUntukApproval'));
    }
    
    /**
     * Display riwayat approval page.
     */
    public function riwayatApproval()
    {
        $statusDisetujuiId = StatusWo::where('nama_status', 'Disetujui Atasan')->value('id_status_wo');
        $statusDitolakId = StatusWo::where('nama_status', 'Ditolak Atasan')->value('id_status_wo');

        $riwayatApproval = PermintaanBarang::with(['suratPengajuan', 'akun', 'statusWo', 'daftarBarang'])
            ->whereIn('id_status_wo', [$statusDisetujuiId, $statusDitolakId])
            ->orderBy('updated_at', 'desc')
            ->get();
        
        return view('atasan.riwayat_approval', compact('riwayatApproval'));
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
        $userDivisi = Session::get('user_divisi');
        $userDivisiNama = DB::table('divisi')->where('id_divisi', $userDivisi)->value('nama_divisi') ?? 'Atasan';
        
        // WO yang diterima oleh Atasan (dari Purchasing)
        $workOrders = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder', 'parent'])
            ->where('ditujukan', $userDivisiNama)
            ->where('divisi_pengaju', '!=', $userDivisiNama)
            ->where('id_verifikator', 1) // Hanya status Menunggu
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('atasan.work_order_masuk', compact('workOrders'));
    }

    /**
     * Display riwayat work order page.
     */
    public function riwayatWorkOrder()
    {
        $userDivisi = Session::get('user_divisi');
        $userDivisiNama = DB::table('divisi')->where('id_divisi', $userDivisi)->value('nama_divisi') ?? 'Atasan';
        
        // WO yang dibuat dan diterima oleh Atasan
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
        
        return view('atasan.riwayat_work_order', compact('workOrders'));
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
            $redirectUrl = route('atasan.work-order-masuk', ['from' => 'crud']);
            
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
                'redirect' => route('atasan.work-order-masuk', ['from' => 'crud'])
            ], 500);
        }
    }
    
    /**
     * Reject work order.
     */
    public function rejectWorkOrder(Request $request, $id)
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
                    'redirect' => route('atasan.work-order-masuk', ['from' => 'crud'])
                ], 403);
            }
            
            $workOrder->update([
                'id_verifikator' => 3, // 3 = Ditolak
                'status' => 'Ditolak',
                'catatan_penolakan' => $request->catatan_penolakan ?? null,
            ]);
            
            // Set session message untuk notifikasi toast
            Session::flash('success', 'Work Order berhasil ditolak!');
            
            // Return JSON untuk AJAX dengan redirect URL yang sudah include from=crud
            $redirectUrl = route('atasan.work-order-masuk', ['from' => 'crud']);
            
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
                'redirect' => route('atasan.work-order-masuk', ['from' => 'crud'])
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
        
        return view('atasan.profile', compact('user'));
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
            
            return redirect()->route('atasan.profile', ['from' => 'crud'])->with('success', 'Data berhasil diperbarui');
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
            
            return redirect()->route('atasan.profile', ['from' => 'crud'])->with('success', 'Password berhasil diubah!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengubah password: ' . $e->getMessage());
        }
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
            'parent'
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
