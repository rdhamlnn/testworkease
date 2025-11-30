<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PeranSeeder extends Seeder
{
    public function run(): void
    {
        $peran = [
            ['nama_peran' => 'Admin'],
            ['nama_peran' => 'Kadiv'],
            ['nama_peran' => 'Kadiv Produksi'],
            ['nama_peran' => 'Kadiv Mekanik'],
            ['nama_peran' => 'Logistik'],
            ['nama_peran' => 'Purchasing'],
            ['nama_peran' => 'Quality Control'],
            ['nama_peran' => 'Mekanik'],
            ['nama_peran' => 'Karyawan'],
            ['nama_peran' => 'Atasan'],
        ];

        foreach ($peran as $p) {
            DB::table('peran')->updateOrInsert(
                ['nama_peran' => $p['nama_peran']],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}