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
            'menungguApproval', 'disetujui', 'ditolak',
            'monthlyApprovalTrend', 'approvalPercentage', 'recentActivities'
        ));
    }
    
    public function approvalPermintaan()
    {
        $statusMenungguApprovalId = $this->getStatusId('Menunggu Approval Atasan');

        $permintaanUntukApproval = PermintaanBarang::with(['suratPengajuan', 'akun', 'statusWo', 'daftarBarang'])
            ->forStatus($statusMenungguApprovalId)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('atasan.approval_permintaan', compact('permintaanUntukApproval'));
    }
    
    public function riwayatApproval()
    {
        $statusDisetujuiId = $this->getStatusId('Disetujui Atasan');
        $statusDitolakId = $this->getStatusId('Ditolak Atasan');

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
        $userDivisiNama = DB::table('divisi')->where('id_divisi', Session::get('user_divisi'))->value('nama_divisi') ?? 'Atasan';
        
        $workOrders = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder', 'parent'])
            ->toDivisi($userDivisiNama)
            ->where('divisi_pengaju', '!=', $userDivisiNama)
            ->byVerifikator(1)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('atasan.work_order_masuk', compact('workOrders'));
    }

    public function riwayatWorkOrder()
    {
        $userDivisiNama = DB::table('divisi')->where('id_divisi', Session::get('user_divisi'))->value('nama_divisi') ?? 'Atasan';
        
        $workOrders = SuratPengajuan::with(['divisi', 'unit', 'akun', 'verifikator', 'jenisWorkOrder', 'parent'])
            ->dibuatAtauDiterima($userDivisiNama)
            ->byVerifikator([2, 3])
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('atasan.riwayat_work_order', compact('workOrders'));
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
