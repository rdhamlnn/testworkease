<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function adminDashboard()
    {
        // Ambil data user dari session dan database
        $userId = Session::get('user_id');
        
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Ambil data lengkap user dari database
        $user = DB::table('akun')
            ->join('peran', 'akun.id_peran', '=', 'peran.id_peran')
            ->join('karyawan', 'akun.id_karyawan', '=', 'karyawan.id_karyawan')
            ->join('divisi', 'akun.id_divisi', '=', 'divisi.id_divisi')
            ->where('akun.id_akun', $userId)
            ->select('akun.*', 'peran.nama_peran', 'karyawan.nama_lengkap', 'divisi.nama_divisi')
            ->first();

        if (!$user) {
            Session::flush();
            return redirect()->route('login')->with('error', 'Akun tidak valid.');
        }

        // ========== DATA CHART REAL DARI DATABASE ==========
        
        // 1. Statistik Dashboard
        $totalWorkOrder = DB::table('surat_pengajuan')->count();
        $totalLaporanHarian = DB::table('laporan_harian_mekanik')->count();
        $totalLaporanBarang = DB::table('laporan_pemakaian_barang')->count();
        $totalAkun = DB::table('akun')->count();
        
        // 2. Status Work Order Chart
        $workOrderStatus = DB::table('surat_pengajuan')
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->pluck('total', 'status')
            ->toArray();

        // 3. Trend Laporan Harian Mekanik (6 bulan terakhir) - Hanya jika ada data
        $monthlyReports = DB::table('laporan_harian_mekanik')
            ->select(
                DB::raw('MONTH(tanggal) as month'),
                DB::raw('YEAR(tanggal) as year'),
                DB::raw('count(*) as total')
            )
            ->where('tanggal', '>=', DB::raw('DATE_SUB(NOW(), INTERVAL 6 MONTH)'))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // 4. Distribusi User per Divisi
        $userDistribution = DB::table('akun')
            ->join('divisi', 'akun.id_divisi', '=', 'divisi.id_divisi')
            ->select('divisi.nama_divisi', DB::raw('count(*) as total'))
            ->groupBy('divisi.nama_divisi')
            ->get()
            ->pluck('total', 'nama_divisi')
            ->toArray();

        // 5. Aktivitas Terbaru
        $recentActivities = DB::table('surat_pengajuan')
            ->join('karyawan', 'surat_pengajuan.id_akun', '=', 'karyawan.id_karyawan')
            ->select('surat_pengajuan.*', 'karyawan.nama_lengkap')
            ->orderBy('surat_pengajuan.created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'judul' => 'Admin Dashboard',
            'user' => $user,
            'totalWorkOrder' => $totalWorkOrder,
            'totalLaporanHarian' => $totalLaporanHarian,
            'totalLaporanBarang' => $totalLaporanBarang,
            'totalAkun' => $totalAkun,
            'workOrderStatus' => $workOrderStatus,
            'monthlyReports' => $monthlyReports,
            'userDistribution' => $userDistribution,
            'recentActivities' => $recentActivities
        ]);
    }

    /**
     * Show user dashboard
     */
    public function userDashboard()
    {
        // Ambil data user dari session dan database
        $userId = Session::get('user_id');
        
        if (!$userId) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Ambil data lengkap user dari database
        $user = DB::table('akun')
            ->join('peran', 'akun.id_peran', '=', 'peran.id_peran')
            ->join('karyawan', 'akun.id_karyawan', '=', 'karyawan.id_karyawan')
            ->join('divisi', 'akun.id_divisi', '=', 'divisi.id_divisi')
            ->where('akun.id_akun', $userId)
            ->select('akun.*', 'peran.nama_peran', 'karyawan.nama_lengkap', 'divisi.nama_divisi')
            ->first();

        if (!$user) {
            Session::flush();
            return redirect()->route('login')->with('error', 'Akun tidak valid.');
        }

        return view('user.dashboard', [
            'judul' => 'User Dashboard',
            'user' => $user
        ]);
    }
}
