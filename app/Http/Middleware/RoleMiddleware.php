<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

/**
 * Middleware untuk validasi role user berdasarkan database
 * Menggantikan CheckAuth, EnsureUserIsAdmin, dan EnsureUserIsKadivMekanik
 * 
 * Cara pakai:
 * - Route::middleware(['role'])->group(...) // Cek login saja
 * - Route::middleware(['role:admin'])->group(...) // Cek admin (id_peran = 1)
 * - Route::middleware(['role:kadiv'])->group(...) // Cek kadiv (id_peran = 2)
 * - Route::middleware(['role:user'])->group(...) // Cek user/karyawan (id_peran = 3)
 * - Route::middleware(['role:mekanik'])->group(...) // Cek mekanik (id_peran = 4)
 * - Route::middleware(['role:admin,kadiv'])->group(...) // Cek admin ATAU kadiv
 */
class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // 1. Cek apakah user sudah login (auth middleware should handle this)
        if (!Session::has('user_id')) {
            return redirect()->route('login')
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        $userId = Session::get('user_id');

        // 2. Ambil data akun terbaru dari database dengan JOIN tabel peran dan divisi
        $akun = DB::table('akun')
            ->join('peran', 'akun.id_peran', '=', 'peran.id_peran')
            ->join('divisi', 'akun.id_divisi', '=', 'divisi.id_divisi')
            ->where('akun.id_akun', $userId)
            ->select('akun.*', 'peran.nama_peran', 'divisi.nama_divisi')
            ->first();

        // 3. Jika akun tidak ditemukan di database, logout dan redirect
        if (!$akun) {
            Session::flush();
            return redirect()->route('login')
                ->with('error', 'Akun tidak valid. Silakan login kembali.');
        }

        // 4. Update session dengan data terbaru dari database
        Session::put('user_peran', $akun->id_peran);
        Session::put('user_email', $akun->email);
        Session::put('user_divisi', $akun->id_divisi);
        Session::put('user_karyawan', $akun->id_karyawan);

        // 5. Jika tidak ada role requirement, izinkan akses (hanya cek login)
        if (empty($roles)) {
            return $next($request);
        }

        // 6. Validasi role berdasarkan id_peran dari database
        $userRoleId = $akun->id_peran;
        $hasAccess = false;

        foreach ($roles as $requiredRole) {
            $requiredRole = strtolower(trim($requiredRole));
            
            switch ($requiredRole) {
                case 'admin':
                    if ($userRoleId == 1) {
                        $hasAccess = true;
                    }
                    break;
                
                case 'atasan':
                    if ($userRoleId == 4 || $userRoleId == 7 || $userRoleId == 10) { // Support both id lama dan id baru Atasan
                        $hasAccess = true;
                    }
                    break;
                
                case 'kadiv':
                case 'kadivmekanik':
                    if ($userRoleId == 2) {
                        $hasAccess = true;
                    }
                    break;
                
                case 'user':
                case 'karyawan':
                case 'mekanik':
                    // Dukungan untuk beberapa id_peran yang merepresentasikan Karyawan/Mekanik
                    if (in_array($userRoleId, [3, 8, 9], true)) {
                        $hasAccess = true;
                    }
                    break;
            }

            // Jika sudah dapat akses, langsung break
            if ($hasAccess) {
                break;
            }
        }

        // 7. Jika punya akses, lanjutkan request
        if ($hasAccess) {
            return $next($request);
        }

        // 8. Jika tidak punya akses, redirect ke dashboard yang sesuai dengan rolenya
        return $this->redirectToDashboard($akun->id_peran, $akun->id_divisi, $akun->nama_divisi ?? '');
    }

    /**
     * Redirect user ke dashboard yang sesuai dengan rolenya
     *
     * @param  int  $roleId
     * @param  int  $divisiId
     * @param  string  $namaDivisi
     * @return \Illuminate\Http\RedirectResponse
     */
    private function redirectToDashboard($roleId, $divisiId, $namaDivisi)
    {
        $errorMessage = 'Anda tidak memiliki akses ke halaman ini.';
        $namaDivisi = strtolower($namaDivisi);

        switch ($roleId) {
            case 1: // Admin
                return redirect()->route('admin.dashboard')
                    ->with('error', $errorMessage);
            
            case 2: // Kadiv / Logistik / Purchasing / Kadiv Divisi Lain
                switch ($namaDivisi) {
                    case 'logistik':
                        return redirect()->route('logistik.dashboard')
                            ->with('error', $errorMessage);
                    case 'purchasing':
                        return redirect()->route('purchasing.dashboard')
                            ->with('error', $errorMessage);
                    case 'produksi':
                        return redirect()->route('kadivproduksi.dashboard')
                            ->with('error', $errorMessage);
                    case 'plasma':
                        return redirect()->route('kadivplasma.dashboard')
                            ->with('error', $errorMessage);
                    case 'quality control':
                    case 'qc':
                        return redirect()->route('kadivqc.dashboard')
                            ->with('error', $errorMessage);
                    default: // Mekanik
                        return redirect()->route('kadivmekanik.dashboard')
                            ->with('error', $errorMessage);
                }
            
            case 3: // Karyawan/User/Mekanik
                return redirect()->route('mekanik.dashboard')
                    ->with('error', $errorMessage);
            
            case 4: // Atasan (id_peran lama)
            case 7: // Atasan (id_peran lama lain)
            case 10: // Atasan (id_peran sesuai PeranSeeder saat ini)
                return redirect()->route('atasan.dashboard')
                    ->with('error', $errorMessage);
            
            default:
                return redirect()->route('login')
                    ->with('error', 'Role tidak valid.');
        }
    }
}

