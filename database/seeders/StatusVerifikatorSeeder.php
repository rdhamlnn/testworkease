<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusVerifikatorSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['nama_status' => 'Menunggu'],
            ['nama_status' => 'Disetujui'],
            ['nama_status' => 'Ditolak'],
        ];

        foreach ($statuses as $status) {
            DB::table('status_verifikator')->updateOrInsert(
                ['nama_status' => $status['nama_status']],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}