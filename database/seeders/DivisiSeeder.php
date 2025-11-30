<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisiSeeder extends Seeder
{
    public function run(): void
    {
        $divisi = [
            ['id_divisi' => '1', 'nama_divisi' => 'Logistik'],
            ['id_divisi' => '2', 'nama_divisi' => 'Mekanik'],
            ['id_divisi' => '3', 'nama_divisi' => 'Produksi'],
            ['id_divisi' => '4', 'nama_divisi' => 'Plasma'],
            ['id_divisi' => '5', 'nama_divisi' => 'Purchasing'],
            ['id_divisi' => '6', 'nama_divisi' => 'Quality Control'],
            ['id_divisi' => '7', 'nama_divisi' => 'Administrator'],
            ['id_divisi' => '8', 'nama_divisi' => 'Atasan'],
        ];

        foreach ($divisi as $d) {
            DB::table('divisi')->updateOrInsert(
                ['id_divisi' => $d['id_divisi']],
                [
                    'nama_divisi' => $d['nama_divisi'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
