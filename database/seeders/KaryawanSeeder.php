<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KaryawanSeeder extends Seeder
{
    public function run(): void
    {

        // Rudi Santoso - Administrator (divisi Administrator)
        $adminDivisi = DB::table('divisi')->where('nama_divisi', 'Administrator')->first();
        $adminDivisiId = $adminDivisi->id_divisi ?? 7;

        // Budi Hermawan dan Ahmad Wijaya - Mekanik (divisi Mekanik)
        $mekanikDivisi = DB::table('divisi')->where('nama_divisi', 'Mekanik')->first();
        $mekanikDivisiId = $mekanikDivisi->id_divisi ?? 2;

        // Get divisions untuk divisi baru
        $logistikDivisi = DB::table('divisi')->where('nama_divisi', 'Logistik')->first();
        $purchasingDivisi = DB::table('divisi')->where('nama_divisi', 'Purchasing')->first();
        $produksiDivisi = DB::table('divisi')->where('nama_divisi', 'Produksi')->first();
        $plasmaDivisi = DB::table('divisi')->where('nama_divisi', 'Plasma')->first();
        $qcDivisi = DB::table('divisi')->where('nama_divisi', 'Quality Control')->first();
        $atasanDivisi = DB::table('divisi')->where('nama_divisi', 'Atasan')->first();
        
        $logistikDivisiId = $logistikDivisi->id_divisi ?? 1;
        $purchasingDivisiId = $purchasingDivisi->id_divisi ?? 5;
        $produksiDivisiId = $produksiDivisi->id_divisi ?? 3;
        $plasmaDivisiId = $plasmaDivisi->id_divisi ?? 4;
        $qcDivisiId = $qcDivisi->id_divisi ?? 6;
        $atasanDivisiId = $atasanDivisi->id_divisi ?? 8;

        $karyawan = [
            [
                'id_karyawan' => '1',
                'nama_lengkap' => 'Rudi Santoso',
                'alamat' => 'Jl. Merpati No.1',
                'no_hp' => '081234567890',
                'jabatan' => 'Administrator',
                'id_divisi' => $adminDivisiId,
            ],
            [
                'id_karyawan' => '2',
                'nama_lengkap' => 'Budi Hermawan',
                'alamat' => 'Jl. Elang No.2',
                'no_hp' => '081234567891',
                'jabatan' => 'Kepala Divisi Mekanik',
                'id_divisi' => $mekanikDivisiId,
            ],
            [
                'id_karyawan' => '3',
                'nama_lengkap' => 'Ahmad Wijaya',
                'alamat' => 'Jl. Rajawali No.3',
                'no_hp' => '081234567892',
                'jabatan' => 'Mekanik',
                'id_divisi' => $mekanikDivisiId,
            ],
            [
                'id_karyawan' => '4',
                'nama_lengkap' => 'Karyawan Logistik',
                'alamat' => 'Jl. Logistik No.1',
                'no_hp' => '081234567893',
                'jabatan' => 'Logistik',
                'id_divisi' => $logistikDivisiId,
            ],
            [
                'id_karyawan' => '5',
                'nama_lengkap' => 'Karyawan Purchasing',
                'alamat' => 'Jl. Purchasing No.1',
                'no_hp' => '081234567894',
                'jabatan' => 'Purchasing',
                'id_divisi' => $purchasingDivisiId,
            ],
            [
                'id_karyawan' => '6',
                'nama_lengkap' => 'Karyawan Produksi',
                'alamat' => 'Jl. Produksi No.1',
                'no_hp' => '081234567895',
                'jabatan' => 'Kepala Divisi Produksi',
                'id_divisi' => $produksiDivisiId,
            ],
            [
                'id_karyawan' => '7',
                'nama_lengkap' => 'Karyawan Plasma',
                'alamat' => 'Jl. Plasma No.1',
                'no_hp' => '081234567896',
                'jabatan' => 'Kepala Divisi Plasma',
                'id_divisi' => $plasmaDivisiId,
            ],
            [
                'id_karyawan' => '8',
                'nama_lengkap' => 'Karyawan QC',
                'alamat' => 'Jl. QC No.1',
                'no_hp' => '081234567897',
                'jabatan' => 'Kepala Divisi Quality Control',
                'id_divisi' => $qcDivisiId,
            ],
            [
                'id_karyawan' => '9',
                'nama_lengkap' => 'Karyawan Atasan',
                'alamat' => 'Jl. Atasan No.1',
                'no_hp' => '081234567898',
                'jabatan' => 'Atasan',
                'id_divisi' => $atasanDivisiId,
            ],
        ];

        foreach ($karyawan as $k) {
            DB::table('karyawan')->updateOrInsert(
                ['id_karyawan' => $k['id_karyawan']],
                array_merge($k, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}