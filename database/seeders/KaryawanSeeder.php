<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Karyawan;

class KaryawanSeeder extends Seeder
{
    public function run(): void
    {
        $adminDivisi = DB::table('divisi')->where('nama_divisi', 'Administrator')->first();
        $adminDivisiId = $adminDivisi->id_divisi ?? 7;
        $mekanikDivisi = DB::table('divisi')->where('nama_divisi', 'Mekanik')->first();
        $mekanikDivisiId = $mekanikDivisi->id_divisi ?? 2;

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

        // Use model method to upsert each karyawan
        $karyawanList = [];
        for ($id = 1; $id <= 9; $id++) {
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
                default => 'Karyawan',
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
                default => 'Karyawan',
            };

            $noHp = '08123456789' . ($id - 1);
            $divisiId = match($id) {
                1 => $adminDivisiId,
                2, 3 => $mekanikDivisiId,
                4 => $logistikDivisiId,
                5 => $purchasingDivisiId,
                6 => $produksiDivisiId,
                7 => $plasmaDivisiId,
                8 => $qcDivisiId,
                9 => $atasanDivisiId,
                default => 1,
            };
            // Upsert via model
            Karyawan::upsertKaryawan($id, $namaLengkap, 'Jl. ' . $jabatan . ' No.' . $id, $noHp, $jabatan, $divisiId);
            // Retrieve the upserted record for later use
            $karyawan = DB::table('karyawan')->where('id_karyawan', $id)->first();
            $karyawanList[$id] = $karyawan;
        }
    }
}