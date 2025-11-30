<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Akun;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        return view('login.login_form');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 6 karakter',
        ]);

        $email = $request->email;
        $password = $request->password;

        // Cari user di tabel akun dengan JOIN peran, divisi, dan karyawan untuk mendapatkan nama peran, divisi, dan jabatan
        $akun = DB::table('akun')
            ->join('peran', 'akun.id_peran', '=', 'peran.id_peran')
            ->join('divisi', 'akun.id_divisi', '=', 'divisi.id_divisi')
            ->join('karyawan', 'akun.id_karyawan', '=', 'karyawan.id_karyawan')
            ->where('akun.email', $email)
            ->select('akun.*', 'peran.nama_peran', 'divisi.nama_divisi', 'karyawan.nama_lengkap', 'karyawan.jabatan')
            ->first();

        if ($akun && Hash::check($password, $akun->password)) {
            // Store user data in session
            Session::put('user_id', $akun->id_akun);
            Session::put('user_email', $akun->email);
            Session::put('user_divisi', $akun->id_divisi);
            Session::put('user_peran', $akun->id_peran);
            Session::put('user_karyawan', $akun->id_karyawan);
            Session::put('nama_lengkap', $akun->nama_lengkap);
            Session::put('jabatan', $akun->jabatan);

            // Force session save
            Session::save();

            $request->session()->regenerate();

            // Redirect berdasarkan id_peran dan nama_divisi dari database
            switch ($akun->id_peran) {
                case 1: // Admin
                    return redirect()->route('admin.dashboard', ['from' => 'login'])
                        ->with('success', 'Login berhasil! Selamat datang Admin.');
                
                case 2: // Kadiv / Logistik / Purchasing / Kadiv Divisi Lain
                    // Ambil nama divisi
                    $namaDivisi = strtolower($akun->nama_divisi ?? '');
                    
                    if ($namaDivisi === 'logistik') {
                        return redirect()->route('logistik.dashboard', ['from' => 'login'])
                            ->with('success', 'Login berhasil! Selamat datang Logistik.');
                    }
                    if ($namaDivisi === 'purchasing') {
                        return redirect()->route('purchasing.dashboard', ['from' => 'login'])
                            ->with('success', 'Login berhasil! Selamat datang Purchasing.');
                    }
                    if ($namaDivisi === 'produksi') {
                        return redirect()->route('kadivproduksi.dashboard', ['from' => 'login'])
                            ->with('success', 'Login berhasil! Selamat datang Kadiv Produksi.');
                    }
                    if ($namaDivisi === 'plasma') {
                        return redirect()->route('kadivplasma.dashboard', ['from' => 'login'])
                            ->with('success', 'Login berhasil! Selamat datang Kadiv Plasma.');
                    }
                    if ($namaDivisi === 'quality control' || $namaDivisi === 'qc') {
                        return redirect()->route('kadivqc.dashboard', ['from' => 'login'])
                            ->with('success', 'Login berhasil! Selamat datang Kadiv Quality Control.');
                    }
                    // Default untuk kadivmekanik (divisi Mekanik)
                    return redirect()->route('kadivmekanik.dashboard', ['from' => 'login'])
                        ->with('success', 'Login berhasil! Selamat datang Kadiv Mekanik.');
                
                case 3: // Karyawan/Mekanik (id lama, jika masih ada)
                case 8: // Mekanik (sesuai urutan PeranSeeder saat ini)
                case 9: // Karyawan (sesuai urutan PeranSeeder saat ini)
                    return redirect()->route('mekanik.dashboard', ['from' => 'login'])
                        ->with('success', 'Login berhasil! Selamat datang ' . $akun->nama_peran . '.');
                
                case 4: // Atasan (versi lama)
                case 7: // Atasan (versi lama lain)
                case 10: // Atasan (sesuai seeder PeranSeeder saat ini)
                    return redirect()->route('atasan.dashboard', ['from' => 'login'])
                        ->with('success', 'Login berhasil! Selamat datang Atasan.');
                
                default:
                    return redirect()->route('login')
                        ->with('error', 'Role tidak valid.');
            }
        }

        return back()->withErrors([
            'email' => 'Email atau password tidak sesuai.',
        ])->withInput($request->except('password'));
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login', ['from' => 'logout'])->with('error', 'Logout berhasil! Terima kasih telah menggunakan WorkEase KCE.');
    }

    /**
     * Create default admin user if not exists
     */
    public function createDefaultAdmin()
    {
        $adminExists = User::where('email', 'admin@kce.com')->exists();
        
        if (!$adminExists) {
            User::create([
                'name' => 'Administrator',
                'email' => 'admin@kce.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]);
            
            return 'Admin user created successfully!';
        }
        
        return 'Admin user already exists!';
    }

    /**
     * Create default kadiv mekanik user if not exists
     */
    public function createDefaultKadivMekanik()
    {
        $kadivExists = DB::table('akun')->where('email', 'kadivmekanik@kce.com')->exists();
        
        if (!$kadivExists) {
            // Check if required data exists
            $divisi = DB::table('divisi')->first();
            $peran = DB::table('peran')->where('nama_peran', 'like', '%kadiv%')->orWhere('nama_peran', 'like', '%mekanik%')->first();
            $karyawan = DB::table('karyawan')->first();
            
            if (!$divisi || !$peran || !$karyawan) {
                return 'Required data (divisi, peran, or karyawan) not found. Please run seeders first.';
            }
            
            DB::table('akun')->insert([
                'email' => 'kadivmekanik@kce.com',
                'password' => Hash::make('password'),
                'id_divisi' => $divisi->id_divisi,
                'id_peran' => $peran->id_peran,
                'id_karyawan' => $karyawan->id_karyawan,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return 'Kadiv Mekanik user created successfully!<br>Email: kadivmekanik@kce.com<br>Password: password';
        }
        
        return 'Kadiv Mekanik user already exists!';
    }

    /**
     * Create default mekanik user if not exists
     */
    public function createDefaultMekanik()
    {
        $existingUser = DB::table('akun')->where('email', 'mekanik@kce.com')->first();
        
        if (!$existingUser) {
            // Get first divisi and karyawan
            $divisi = DB::table('divisi')->first();
            $karyawan = DB::table('karyawan')->first();
            $peran = DB::table('peran')->where('nama_peran', 'Mekanik')->first();
            
            if (!$peran) {
                // Create Mekanik role if not exists
                $peranId = DB::table('peran')->insertGetId([
                    'nama_peran' => 'Mekanik',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $peran = (object)['id_peran' => $peranId];
            }
            
            DB::table('akun')->insert([
                'email' => 'mekanik@kce.com',
                'password' => Hash::make('password123'),
                'id_divisi' => $divisi->id_divisi,
                'id_peran' => $peran->id_peran,
                'id_karyawan' => $karyawan->id_karyawan,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return 'Mekanik user created successfully!<br>Email: mekanik@kce.com<br>Password: password';
        }
        
        return 'Mekanik user already exists!';
    }

    /**
     * Create default logistik user if not exists
     */
    public function createDefaultLogistik()
    {
        $logistikExists = DB::table('akun')->where('email', 'logistik@kce.com')->exists();
        
        if (!$logistikExists) {
            $divisi = DB::table('divisi')->where('nama_divisi', 'Logistik')->first();
            $peran = DB::table('peran')->where('id_peran', 2)->first(); // Kadiv
            $karyawan = DB::table('karyawan')->first();
            
            if (!$divisi || !$peran || !$karyawan) {
                return 'Required data (divisi, peran, or karyawan) not found. Please run seeders first.';
            }
            
            DB::table('akun')->insert([
                'email' => 'logistik@kce.com',
                'password' => Hash::make('password'),
                'id_divisi' => $divisi->id_divisi,
                'id_peran' => $peran->id_peran,
                'id_karyawan' => $karyawan->id_karyawan,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return 'Logistik user created successfully!<br>Email: logistik@kce.com<br>Password: password';
        }
        
        return 'Logistik user already exists!';
    }

    /**
     * Create default purchasing user if not exists
     */
    public function createDefaultPurchasing()
    {
        $purchasingExists = DB::table('akun')->where('email', 'purchasing@kce.com')->exists();
        
        if (!$purchasingExists) {
            $divisi = DB::table('divisi')->where('nama_divisi', 'Purchasing')->first();
            $peran = DB::table('peran')->where('id_peran', 2)->first(); // Kadiv
            $karyawan = DB::table('karyawan')->first();
            
            if (!$divisi || !$peran || !$karyawan) {
                return 'Required data (divisi, peran, or karyawan) not found. Please run seeders first.';
            }
            
            DB::table('akun')->insert([
                'email' => 'purchasing@kce.com',
                'password' => Hash::make('password'),
                'id_divisi' => $divisi->id_divisi,
                'id_peran' => $peran->id_peran,
                'id_karyawan' => $karyawan->id_karyawan,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return 'Purchasing user created successfully!<br>Email: purchasing@kce.com<br>Password: password';
        }
        
        return 'Purchasing user already exists!';
    }

    /**
     * Create default atasan user if not exists
     */
    public function createDefaultAtasan()
    {
        $atasanExists = DB::table('akun')->where('email', 'atasan@kce.com')->exists();
        
        if (!$atasanExists) {
            $divisi = DB::table('divisi')->where('nama_divisi', 'Administrator')->first();
            $peran = DB::table('peran')->where('id_peran', 1)->first(); // Admin
            $karyawan = DB::table('karyawan')->first();
            
            if (!$divisi || !$peran || !$karyawan) {
                return 'Required data (divisi, peran, or karyawan) not found. Please run seeders first.';
            }
            
            DB::table('akun')->insert([
                'email' => 'atasan@kce.com',
                'password' => Hash::make('password'),
                'id_divisi' => $divisi->id_divisi,
                'id_peran' => $peran->id_peran,
                'id_karyawan' => $karyawan->id_karyawan,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return 'Atasan user created successfully!<br>Email: atasan@kce.com<br>Password: password';
        }
        
        return 'Atasan user already exists!';
    }

    /**
     * Create default kadiv produksi user if not exists
     */
    public function createDefaultKadivProduksi()
    {
        $kadivExists = DB::table('akun')->where('email', 'kadivproduksi@kce.com')->exists();
        
        if (!$kadivExists) {
            $divisi = DB::table('divisi')->where('nama_divisi', 'Produksi')->first();
            $peran = DB::table('peran')->where('id_peran', 2)->first(); // Kadiv
            $karyawan = DB::table('karyawan')->first();
            
            if (!$divisi || !$peran || !$karyawan) {
                return 'Required data (divisi, peran, or karyawan) not found. Please run seeders first.';
            }
            
            DB::table('akun')->insert([
                'email' => 'kadivproduksi@kce.com',
                'password' => Hash::make('password'),
                'id_divisi' => $divisi->id_divisi,
                'id_peran' => $peran->id_peran,
                'id_karyawan' => $karyawan->id_karyawan,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return 'Kadiv Produksi user created successfully!<br>Email: kadivproduksi@kce.com<br>Password: password';
        }
        
        return 'Kadiv Produksi user already exists!';
    }

    /**
     * Create default kadiv plasma user if not exists
     */
    public function createDefaultKadivPlasma()
    {
        $kadivExists = DB::table('akun')->where('email', 'kadivplasma@kce.com')->exists();
        
        if (!$kadivExists) {
            $divisi = DB::table('divisi')->where('nama_divisi', 'Plasma')->first();
            $peran = DB::table('peran')->where('id_peran', 2)->first(); // Kadiv
            $karyawan = DB::table('karyawan')->first();
            
            if (!$divisi || !$peran || !$karyawan) {
                return 'Required data (divisi, peran, or karyawan) not found. Please run seeders first.';
            }
            
            DB::table('akun')->insert([
                'email' => 'kadivplasma@kce.com',
                'password' => Hash::make('password'),
                'id_divisi' => $divisi->id_divisi,
                'id_peran' => $peran->id_peran,
                'id_karyawan' => $karyawan->id_karyawan,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return 'Kadiv Plasma user created successfully!<br>Email: kadivplasma@kce.com<br>Password: password';
        }
        
        return 'Kadiv Plasma user already exists!';
    }

    /**
     * Create default kadiv qc user if not exists
     */
    public function createDefaultKadivQc()
    {
        $kadivExists = DB::table('akun')->where('email', 'kadivqc@kce.com')->exists();
        
        if (!$kadivExists) {
            $divisi = DB::table('divisi')->where('nama_divisi', 'Quality Control')->first();
            $peran = DB::table('peran')->where('id_peran', 2)->first(); // Kadiv
            $karyawan = DB::table('karyawan')->first();
            
            if (!$divisi || !$peran || !$karyawan) {
                return 'Required data (divisi, peran, or karyawan) not found. Please run seeders first.';
            }
            
            DB::table('akun')->insert([
                'email' => 'kadivqc@kce.com',
                'password' => Hash::make('password'),
                'id_divisi' => $divisi->id_divisi,
                'id_peran' => $peran->id_peran,
                'id_karyawan' => $karyawan->id_karyawan,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return 'Kadiv QC user created successfully!<br>Email: kadivqc@kce.com<br>Password: password';
        }
        
        return 'Kadiv QC user already exists!';
    }
}
