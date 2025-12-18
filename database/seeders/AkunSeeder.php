<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Akun;
use App\Models\Karyawan;

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
        $karyawanIds = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        
        foreach ($karyawanIds as $id) {
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
            
            Karyawan::upsertKaryawan($id, $namaLengkap, 'Jl. ' . $jabatan . ' No.' . $id, $noHp, $jabatan, $divisiId);
        }

        // Definisi data akun
        $akunData = [
            ['email' => 'admin@kce.com', 'id_karyawan' => 1, 'id_divisi' => $adminDivisiId, 'peran' => $adminRole],
            ['email' => 'kadivmekanik@kce.com', 'id_karyawan' => 2, 'id_divisi' => $mekanikDivisiId, 'peran' => $kadivRole],
            ['email' => 'mekanik@kce.com', 'id_karyawan' => 3, 'id_divisi' => $mekanikDivisiId, 'peran' => $karyawanRole],
            ['email' => 'kadivlogistik@kce.com', 'id_karyawan' => 4, 'id_divisi' => $logistikDivisiId, 'peran' => $kadivRole],
            ['email' => 'kadivpurchasing@kce.com', 'id_karyawan' => 5, 'id_divisi' => $purchasingDivisiId, 'peran' => $kadivRole],
            ['email' => 'kadivproduksi@kce.com', 'id_karyawan' => 6, 'id_divisi' => $produksiDivisiId, 'peran' => $kadivRole],
            ['email' => 'kadivplasma@kce.com', 'id_karyawan' => 7, 'id_divisi' => $plasmaDivisiId, 'peran' => $kadivRole],
            ['email' => 'kadivqc@kce.com', 'id_karyawan' => 8, 'id_divisi' => $qcDivisiId, 'peran' => $kadivRole],
            ['email' => 'atasan@kce.com', 'id_karyawan' => 9, 'id_divisi' => $atasanDivisiId, 'peran' => $atasanRole],
        ];

        foreach ($akunData as $data) {
            Akun::upsertAkun(
                $data['email'],
                'password',
                $data['id_karyawan'],
                $data['id_divisi'],
                $data['peran'] ? $data['peran']->id_peran : 1
            );
        }
    }
}