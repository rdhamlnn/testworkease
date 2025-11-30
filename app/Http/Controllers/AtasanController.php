<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use App\Models\PermintaanBarang;
use App\Models\StatusWo;
use Carbon\Carbon;

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
}
