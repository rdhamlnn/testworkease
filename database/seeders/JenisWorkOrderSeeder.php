<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisWorkOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jenisWO = [
            [
                'nama_jenis_wo' => 'Pembelian',
                'deskripsi' => 'Work order untuk pembelian barang atau jasa',
            ],
            [
                'nama_jenis_wo' => 'Perbaikan',
                'deskripsi' => 'Work order untuk perbaikan unit atau peralatan',
            ],
            [
                'nama_jenis_wo' => 'Permintaan',
                'deskripsi' => 'Work order untuk permintaan barang atau jasa',
            ],
        ];

        foreach ($jenisWO as $jenis) {
            DB::table('jenis_work_order')->updateOrInsert(
                ['nama_jenis_wo' => $jenis['nama_jenis_wo']],
                array_merge($jenis, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
