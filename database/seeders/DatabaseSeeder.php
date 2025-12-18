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
            KaryawanSeeder::class,
            AkunSeeder::class,
            SuratPengajuanSeeder::class,
            LaporanHarianMekanikSeeder::class,
            LaporanPemakaianBarangSeeder::class,
            PermintaanBarangSeeder::class,
            DaftarBarangSeeder::class,
            
        ]);
    }
}
