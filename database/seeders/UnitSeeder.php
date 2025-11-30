<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            [
                'nama_unit' => 'TRONTON CRANE 01',
                'kode_unit' => 'KCE T01',
                'no_polisi' => 'DA 8723 TPC',
                'jenis_unit' => 'CRANE PALFINGER',
                'merk_unit' => 'Nissan / CD 520 VN',
                'tahun_pembuatan' => '1997',
            ],
            [
                'nama_unit' => 'TRONTON CRANE 02',
                'kode_unit' => 'KCE T02/INDRA',
                'no_polisi' => 'DA 8747 PW',
                'jenis_unit' => 'CRANE AMCO VEBA',
                'merk_unit' => 'Isuzu / FVM 34 W',
                'tahun_pembuatan' => '2015',
            ],
        ];

        foreach ($units as $unit) {
            DB::table('unit')->updateOrInsert(
                ['kode_unit' => $unit['kode_unit']],
                array_merge($unit, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}