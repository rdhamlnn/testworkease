<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LaporanHarianMekanikSeeder extends Seeder
{
    public function run(): void
    {
        $akun = DB::table('akun')->first();
        $divisi = DB::table('divisi')->first();
        
        // Get unit data from master
        $units = DB::table('unit')->get();
        $unit1 = $units->first();
        $unit2 = $units->skip(1)->first() ?? $unit1;

        $akunId = $akun->id_akun ?? 1;
        $divisiId = $divisi->id_divisi ?? 1;
        $unitId1 = $unit1->id_unit ?? 1;
        $unitId2 = $unit2->id_unit ?? $unitId1;
        $unitName1 = $unit1->nama_unit ?? 'TRONTON CRANE 01';
        $unitName2 = $unit2->nama_unit ?? 'TRONTON CRANE 02';

        $laporan = [
            [
                'tanggal' => now()->subDays(1)->toDateString(),
                'nama_unit' => $unitName1,
                'keluhan_kerusakan' => '[seeders] Sistem hidrolik tidak responsif',
                'penyebab_kerusakan' => '[seeders] Pompa utama aus',
                'tanggal_mulai' => now()->subDays(2)->toDateString(),
                'tanggal_selesai' => now()->subDay()->toDateString(),
                'tindakan_perbaikan' => '[seeders] Ganti pompa dan flushing oli hidrolik',
                'id_akun' => $akunId,
                'id_divisi' => $divisiId,
                'id_unit' => $unitId1,
            ],
            [
                'tanggal' => now()->subDays(3)->toDateString(),
                'nama_unit' => $unitName2,
                'keluhan_kerusakan' => '[seeders] Mesin tidak bisa starter',
                'penyebab_kerusakan' => '[seeders] Baterai soak',
                'tanggal_mulai' => now()->subDays(3)->toDateString(),
                'tanggal_selesai' => now()->subDays(3)->toDateString(),
                'tindakan_perbaikan' => '[seeders] Ganti baterai dan cek alternator',
                'id_akun' => $akunId,
                'id_divisi' => $divisiId,
                'id_unit' => $unitId2,
            ],
            [
                'tanggal' => now()->subDays(4)->toDateString(),
                'nama_unit' => $unitName1,
                'keluhan_kerusakan' => '[seeders] Rem parkir tidak pakem',
                'penyebab_kerusakan' => '[seeders] Kampas rem aus',
                'tanggal_mulai' => now()->subDays(4)->toDateString(),
                'tanggal_selesai' => now()->subDays(3)->toDateString(),
                'tindakan_perbaikan' => '[seeders] Ganti kampas dan setel ulang kabel rem',
                'id_akun' => $akunId,
                'id_divisi' => $divisiId,
                'id_unit' => $unitId1,
            ],
            [
                'tanggal' => now()->subDays(5)->toDateString(),
                'nama_unit' => $unitName2,
                'keluhan_kerusakan' => '[seeders] Setir berat',
                'penyebab_kerusakan' => '[seeders] Oli power steering kurang',
                'tanggal_mulai' => now()->subDays(5)->toDateString(),
                'tanggal_selesai' => now()->subDays(5)->toDateString(),
                'tindakan_perbaikan' => '[seeders] Isi oli dan bleeding sistem',
                'id_akun' => $akunId,
                'id_divisi' => $divisiId,
                'id_unit' => $unitId2,
            ],
            [
                'tanggal' => now()->subDays(6)->toDateString(),
                'nama_unit' => $unitName1,
                'keluhan_kerusakan' => '[seeders] Lampu hazard mati',
                'penyebab_kerusakan' => '[seeders] Sekring putus dan kabel teroksidasi',
                'tanggal_mulai' => now()->subDays(6)->toDateString(),
                'tanggal_selesai' => now()->subDays(6)->toDateString(),
                'tindakan_perbaikan' => '[seeders] Ganti sekring dan perbaiki jalur kabel',
                'id_akun' => $akunId,
                'id_divisi' => $divisiId,
                'id_unit' => $unitId1,
            ],
            [
                'tanggal' => now()->subDays(7)->toDateString(),
                'nama_unit' => $unitName2,
                'keluhan_kerusakan' => '[seeders] Boom bergetar saat operasi',
                'penyebab_kerusakan' => '[seeders] Bushing boom longgar',
                'tanggal_mulai' => now()->subDays(7)->toDateString(),
                'tanggal_selesai' => now()->subDays(6)->toDateString(),
                'tindakan_perbaikan' => '[seeders] Press ulang pin dan ganti bushing',
                'id_akun' => $akunId,
                'id_divisi' => $divisiId,
                'id_unit' => $unitId2,
            ],
        ];

        foreach ($laporan as $lap) {
            DB::table('laporan_harian_mekanik')->updateOrInsert(
                [
                    'tanggal' => $lap['tanggal'],
                    'nama_unit' => $lap['nama_unit'],
                ],
                array_merge($lap, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}