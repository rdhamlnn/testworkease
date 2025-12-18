<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AkunSeeder extends Seeder
{
    public function run(): void
    {
        // Get specific divisions
        $adminDivisi = DB::table('divisi')->where('nama_divisi', 'Administrator')->first();
        $mekanikDivisi = DB::table('divisi')->where('nama_divisi', 'Mekanik')->first();
        $logistikDivisi = DB::table('divisi')->where('nama_divisi', 'Logistik')->first();
        $purchasingDivisi = DB::table('divisi')->where('nama_divisi', 'Purchasing')->first();
        $produksiDivisi = DB::table('divisi')->where('nama_divisi', 'Produksi')->first();
        $plasmaDivisi = DB::table('divisi')->where('nama_divisi', 'Plasma')->first();
        $qcDivisi = DB::table('divisi')->where('nama_divisi', 'Quality Control')->first();
        $atasanDivisi = DB::table('divisi')->where('nama_divisi', 'Atasan')->first();
        
        $adminDivisiId = $adminDivisi->id_divisi ?? 7;
        $mekanikDivisiId = $mekanikDivisi->id_divisi ?? 2;
        $logistikDivisiId = $logistikDivisi->id_divisi ?? 1;
        $purchasingDivisiId = $purchasingDivisi->id_divisi ?? 5;
        $produksiDivisiId = $produksiDivisi->id_divisi ?? 3;
        $plasmaDivisiId = $plasmaDivisi->id_divisi ?? 4;
        $qcDivisiId = $qcDivisi->id_divisi ?? 6;
        $atasanDivisiId = $atasanDivisi->id_divisi ?? 8;

        // Get specific roles (menggunakan data dari tabel peran agar id_peran selalu konsisten)
        $adminRole = DB::table('peran')->where('nama_peran', 'Admin')->first();
        $kadivRole = DB::table('peran')->where('nama_peran', 'Kadiv')->first();
        $karyawanRole = DB::table('peran')->where('nama_peran', 'Karyawan')->first();
        $atasanRole = DB::table('peran')->where('nama_peran', 'Atasan')->first();

        // Pastikan semua karyawan yang diperlukan ada
        $karyawanList = [];
        $karyawanIds = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        
        foreach ($karyawanIds as $id) {
            $karyawan = DB::table('karyawan')->where('id_karyawan', $id)->first();
            if (!$karyawan) {
                // Buat karyawan jika belum ada berdasarkan id
                $divisiId = match($id) {
                    1 => $adminDivisiId,
                    2, 3 => $mekanikDivisiId,
                    4 => $logistikDivisiId,
                    5 => $purchasingDivisiId,
                    6 => $produksiDivisiId,
                    7 => $plasmaDivisiId,
                    8 => $qcDivisiId,
                    9 => $atasanDivisiId,
                    default => 1
                };
                
                // Pastikan divisi ada sebelum insert karyawan
                $divisiExists = DB::table('divisi')->where('id_divisi', $divisiId)->exists();
                if (!$divisiExists) {
                    // Jika divisi tidak ada, tetap isi array dengan fallback object agar tidak error
                    $karyawanList[$id] = (object)['id_karyawan' => $id];
                    continue;
                }
                
                $namaLengkap = match($id) {
                    1 => 'Rudi Santoso',
                    2 => 'Budi Hermawan',
                    3 => 'Ahmad Wijaya',
                    4 => 'Karyawan Logistik',
                    5 => 'Karyawan Purchasing',
                    6 => 'Karyawan Produksi',
                    7 => 'Karyawan Plasma',
                    8 => 'Karyawan QC',
                    9 => 'Karyawan Atasan',
                    default => 'Karyawan'
                };
                
                $jabatan = match($id) {
                    1 => 'Administrator',
                    2 => 'Kepala Divisi Mekanik',
                    3 => 'Mekanik',
                    4 => 'Staff Logistik',
                    5 => 'Staff Purchasing',
                    6 => 'Kepala Divisi Produksi',
                    7 => 'Kepala Divisi Plasma',
                    8 => 'Kepala Divisi Quality Control',
                    9 => 'Atasan',
                    default => 'Karyawan'
                };
                
                $noHp = '08123456789' . ($id - 1);
                
                try {
                    DB::table('karyawan')->insert([
                        'id_karyawan' => $id,
                        'nama_lengkap' => $namaLengkap,
                        'alamat' => 'Jl. ' . $jabatan . ' No.' . $id,
                        'no_hp' => $noHp,
                        'jabatan' => $jabatan,
                        'id_divisi' => $divisiId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    $karyawan = DB::table('karyawan')->where('id_karyawan', $id)->first();
                } catch (\Exception $e) {
                    // Jika insert gagal, coba ambil dari database atau gunakan fallback
                    $karyawan = DB::table('karyawan')->where('id_karyawan', $id)->first();
                    if (!$karyawan) {
                        $karyawan = (object)['id_karyawan' => $id];
                    }
                }
            }
            // Pastikan selalu ada di array meskipun null
            if (!isset($karyawanList[$id])) {
                $karyawanList[$id] = $karyawan;
            }
        }

        // Helper function untuk insert atau update akun
        // Juga update email dari example.com ke kce.com jika ada, dan update email lama dengan format kadiv*
        $upsertAkun = function($email, $password, $idKaryawan, $idDivisi, $idPeran) {
            // Pastikan idPeran tidak null
            if (!$idPeran) {
                return;
            }
            
            // Cek apakah email baru (kce.com) sudah ada
            $exists = DB::table('akun')->where('email', $email)->exists();
            
            if ($exists) {
                // Update dengan relasi yang benar dan reset password untuk memastikan konsistensi
                DB::table('akun')->where('email', $email)->update([
                    'id_karyawan' => $idKaryawan,
                    'id_divisi' => $idDivisi,
                    'id_peran' => $idPeran,
                    'password' => Hash::make($password), // Reset password untuk memastikan konsistensi
                    'updated_at' => now(),
                ]);
            } else {
                // Cek apakah ada email lama dengan format kadiv*@kce.com atau kadiv*@example.com
                $emailParts = explode('@', $email);
                $emailName = $emailParts[0];
                $oldEmailVariants = [
                    'kadiv' . $emailName . '@kce.com',
                    'kadiv' . $emailName . '@example.com',
                    $emailName . '@example.com',
                ];
                
                $oldExists = null;
                $oldEmail = null;
                
                foreach ($oldEmailVariants as $variant) {
                    $found = DB::table('akun')->where('email', $variant)->first();
                    if ($found) {
                        $oldExists = $found;
                        $oldEmail = $variant;
                        break;
                    }
                }
                
                if ($oldExists) {
                    // Update email dari format lama ke format baru dengan reset password
                    DB::table('akun')->where('email', $oldEmail)->update([
                        'email' => $email,
                        'id_karyawan' => $idKaryawan,
                        'id_divisi' => $idDivisi,
                        'id_peran' => $idPeran,
                        'password' => Hash::make($password), // Reset password untuk memastikan konsistensi
                        'updated_at' => now(),
                    ]);
                } else {
                    // Insert baru dengan password
                    DB::table('akun')->insert([
                        'email' => $email,
                        'password' => Hash::make($password),
                        'id_karyawan' => $idKaryawan,
                        'id_divisi' => $idDivisi,
                        'id_peran' => $idPeran,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        };

        // Admin
        $upsertAkun(
            'admin@kce.com',
            'password',
            $karyawanList[1]->id_karyawan ?? 1, // Rudi Santoso - Administrator
            $adminDivisiId,
            $adminRole ? $adminRole->id_peran : 1
        );
        
        // Kadiv Mekanik - Pastikan dibuat dengan benar
        // Pastikan karyawan dengan id_karyawan = 2 ada sebelum membuat akun
        if (!isset($karyawanList[2]) || !$karyawanList[2] || !property_exists($karyawanList[2], 'id_karyawan')) {
            // Buat karyawan jika belum ada
            try {
                DB::table('karyawan')->updateOrInsert(
                    ['id_karyawan' => 2],
                    [
                        'id_karyawan' => 2,
                        'nama_lengkap' => 'Budi Hermawan',
                        'alamat' => 'Jl. Mekanik No.2',
                        'no_hp' => '081234567891',
                        'jabatan' => 'Kepala Divisi Mekanik',
                        'id_divisi' => $mekanikDivisiId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
                $karyawanList[2] = DB::table('karyawan')->where('id_karyawan', 2)->first();
            } catch (\Exception $e) {
                // Jika masih gagal, gunakan fallback
                $karyawanList[2] = (object)['id_karyawan' => 2];
            }
        }
        
        $upsertAkun(
            'kadivmekanik@kce.com',
            'password',
            $karyawanList[2]->id_karyawan ?? 2, // Budi Hermawan - Kepala Divisi Mekanik
            $mekanikDivisiId,
            $kadivRole ? $kadivRole->id_peran : 2
        );
        
        // Mekanik
        $upsertAkun(
            'mekanik@kce.com',
            'password',
            $karyawanList[3]->id_karyawan ?? 3, // Ahmad Wijaya - Mekanik
            $mekanikDivisiId,
            $karyawanRole ? $karyawanRole->id_peran : 3
        );
        
        // Logistik
        $upsertAkun(
            'logistik@kce.com',
            'password',
            $karyawanList[4]->id_karyawan ?? 4, // Karyawan Logistik
            $logistikDivisiId,
            $kadivRole ? $kadivRole->id_peran : 2
        );
        
        // Purchasing
        $upsertAkun(
            'purchasing@kce.com',
            'password',
            $karyawanList[5]->id_karyawan ?? 5, // Karyawan Purchasing
            $purchasingDivisiId,
            $kadivRole ? $kadivRole->id_peran : 2
        );
        
        // Atasan
        $upsertAkun(
            'atasan@kce.com',
            'password',
            $karyawanList[9]->id_karyawan ?? 9, // Karyawan Atasan
            $atasanDivisiId,
            // Gunakan id_peran dari role "Atasan" jika ada, tanpa fallback hardcode
            $atasanRole ? $atasanRole->id_peran : null
        );
        
        // Kadiv Produksi
        $upsertAkun(
            'produksi@kce.com',
            'password',
            $karyawanList[6]->id_karyawan ?? 6, // Karyawan Produksi
            $produksiDivisiId,
            $kadivRole ? $kadivRole->id_peran : 2
        );
        
        // Kadiv Plasma
        $upsertAkun(
            'plasma@kce.com',
            'password',
            $karyawanList[7]->id_karyawan ?? 7, // Karyawan Plasma
            $plasmaDivisiId,
            $kadivRole ? $kadivRole->id_peran : 2
        );
        
        // Kadiv QC
        $upsertAkun(
            'qualitycontrol@kce.com',
            'password',
            $karyawanList[8]->id_karyawan ?? 8, // Karyawan QC
            $qcDivisiId,
            $kadivRole ? $kadivRole->id_peran : 2
        );
    }
}