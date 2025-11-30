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
        $unit = DB::table('unit')->first();

        $akunId = $akun->id_akun ?? 1;
        $divisiId = $divisi->id_divisi ?? 1;
        $unitId = $unit->id_unit ?? 1;

        $laporan = [
            [
                'tanggal' => now()->subDays(1)->toDateString(),
                'nama_unit' => 'FUSO/TRUCK CRANE 02',
                'keluhan_kerusakan' => 'Sistem hidrolik tidak responsif',
                'penyebab_kerusakan' => 'Pompa utama aus',
                'tanggal_mulai' => now()->subDays(2)->toDateString(),
                'tanggal_selesai' => now()->subDay()->toDateString(),
                'tindakan_perbaikan' => 'Ganti pompa dan flushing oli hidrolik',
                'id_akun' => $akunId,
                'id_divisi' => $divisiId,
                'id_unit' => $unitId,
            ],
            [
                'tanggal' => now()->subDays(3)->toDateString(),
                'nama_unit' => 'TRONTON CRANE 01',
                'keluhan_kerusakan' => 'Mesin tidak bisa starter',
                'penyebab_kerusakan' => 'Baterai soak',
                'tanggal_mulai' => now()->subDays(3)->toDateString(),
                'tanggal_selesai' => now()->subDays(3)->toDateString(),
                'tindakan_perbaikan' => 'Ganti baterai dan cek alternator',
                'id_akun' => $akunId,
                'id_divisi' => $divisiId,
                'id_unit' => $unitId,
            ],
            [
                'tanggal' => now()->subDays(4)->toDateString(),
                'nama_unit' => 'TRONTON CRANE 02',
                'keluhan_kerusakan' => 'Rem parkir tidak pakem',
                'penyebab_kerusakan' => 'Kampas rem aus',
                'tanggal_mulai' => now()->subDays(4)->toDateString(),
                'tanggal_selesai' => now()->subDays(3)->toDateString(),
                'tindakan_perbaikan' => 'Ganti kampas dan setel ulang kabel rem',
                'id_akun' => $akunId,
                'id_divisi' => $divisiId,
                'id_unit' => $unitId,
            ],
            [
                'tanggal' => now()->subDays(5)->toDateString(),
                'nama_unit' => 'FORKLIFT 5T',
                'keluhan_kerusakan' => 'Setir berat',
                'penyebab_kerusakan' => 'Oli power steering kurang',
                'tanggal_mulai' => now()->subDays(5)->toDateString(),
                'tanggal_selesai' => now()->subDays(5)->toDateString(),
                'tindakan_perbaikan' => 'Isi oli dan bleeding sistem',
                'id_akun' => $akunId,
                'id_divisi' => $divisiId,
                'id_unit' => $unitId,
            ],
            [
                'tanggal' => now()->subDays(6)->toDateString(),
                'nama_unit' => 'TRAILER LOWBED',
                'keluhan_kerusakan' => 'Lampu hazard mati',
                'penyebab_kerusakan' => 'Sekring putus dan kabel teroksidasi',
                'tanggal_mulai' => now()->subDays(6)->toDateString(),
                'tanggal_selesai' => now()->subDays(6)->toDateString(),
                'tindakan_perbaikan' => 'Ganti sekring dan perbaiki jalur kabel',
                'id_akun' => $akunId,
                'id_divisi' => $divisiId,
                'id_unit' => $unitId,
            ],
            [
                'tanggal' => now()->subDays(7)->toDateString(),
                'nama_unit' => 'EXCAVATOR 320D',
                'keluhan_kerusakan' => 'Boom bergetar saat operasi',
                'penyebab_kerusakan' => 'Bushing boom longgar',
                'tanggal_mulai' => now()->subDays(7)->toDateString(),
                'tanggal_selesai' => now()->subDays(6)->toDateString(),
                'tindakan_perbaikan' => 'Press ulang pin dan ganti bushing',
                'id_akun' => $akunId,
                'id_divisi' => $divisiId,
                'id_unit' => $unitId,
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