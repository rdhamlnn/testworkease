<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // Seeders yang tidak memiliki dependency
            DivisiSeeder::class,
            PeranSeeder::class,
            UnitSeeder::class,
            StatusVerifikatorSeeder::class,
            JenisWorkOrderSeeder::class,
            
            // Seeders yang membutuhkan data dari seeder sebelumnya
            KaryawanSeeder::class,
            
            // Seeders yang membutuhkan data karyawan, divisi, dan peran
            AkunSeeder::class,
            
            // Seeders untuk data tambahan
            SuratPengajuanSeeder::class,
            LaporanHarianMekanikSeeder::class,
            LaporanPemakaianBarangSeeder::class,
            PermintaanBarangSeeder::class,
            DaftarBarangSeeder::class,
            
        ]);
    }
}
