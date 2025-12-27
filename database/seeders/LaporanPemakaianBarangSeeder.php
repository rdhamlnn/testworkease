<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LaporanPemakaianBarangSeeder extends Seeder
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
        $kodeUnit1 = $unit1->kode_unit ?? 'KCE T01';
        $kodeUnit2 = $unit2->kode_unit ?? 'KCE T02/INDRA';

        $laporan = [
            [
                'tanggal' => now()->subDay()->toDateString(),
                'nama_barang' => '[seeders] Selang Hidrolik 3/8 inch',
                'kode_unit' => $kodeUnit1,
                'jumlah' => 3,
                'bentuk_satuan' => 'Meter',
                'harga_satuan' => 45000,
                'total_harga' => 135000,
                'keterangan' => '[seeders] Penggantian selang utama crane',
                'id_akun' => $akunId,
                'id_divisi' => $divisiId,
                'id_unit' => $unitId1,
            ],
            [
                'tanggal' => now()->subDays(2)->toDateString(),
                'nama_barang' => '[seeders] Kampas Rem Depan',
                'kode_unit' => $kodeUnit2,
                'jumlah' => 4,
                'bentuk_satuan' => 'Set',
                'harga_satuan' => 85000,
                'total_harga' => 340000,
                'keterangan' => '[seeders] Perbaikan sistem pengereman',
                'id_akun' => $akunId,
                'id_divisi' => $divisiId,
                'id_unit' => $unitId2,
            ],
            [
                'tanggal' => now()->subDays(3)->toDateString(),
                'nama_barang' => '[seeders] Filter Solar',
                'kode_unit' => $kodeUnit1,
                'jumlah' => 6,
                'bentuk_satuan' => 'Pcs',
                'harga_satuan' => 65000,
                'total_harga' => 390000,
                'keterangan' => '[seeders] Servis berkala 500 jam',
                'id_akun' => $akunId,
                'id_divisi' => $divisiId,
                'id_unit' => $unitId1,
            ],
            [
                'tanggal' => now()->subDays(4)->toDateString(),
                'nama_barang' => '[seeders] Grease EP2',
                'kode_unit' => $kodeUnit2,
                'jumlah' => 10,
                'bentuk_satuan' => 'Tube',
                'harga_satuan' => 42000,
                'total_harga' => 420000,
                'keterangan' => '[seeders] Lubrikasi bearing wheel loader',
                'id_akun' => $akunId,
                'id_divisi' => $divisiId,
                'id_unit' => $unitId2,
            ],
            [
                'tanggal' => now()->subDays(5)->toDateString(),
                'nama_barang' => '[seeders] Lampu LED 24V',
                'kode_unit' => $kodeUnit1,
                'jumlah' => 8,
                'bentuk_satuan' => 'Pcs',
                'harga_satuan' => 115000,
                'total_harga' => 920000,
                'keterangan' => '[seeders] Penggantian lampu hazard',
                'id_akun' => $akunId,
                'id_divisi' => $divisiId,
                'id_unit' => $unitId1,
            ],
            [
                'tanggal' => now()->subDays(6)->toDateString(),
                'nama_barang' => '[seeders] Oil Filter C7',
                'kode_unit' => $kodeUnit2,
                'jumlah' => 2,
                'bentuk_satuan' => 'Pcs',
                'harga_satuan' => 175000,
                'total_harga' => 350000,
                'keterangan' => '[seeders] Maintenance rutin crane',
                'id_akun' => $akunId,
                'id_divisi' => $divisiId,
                'id_unit' => $unitId2,
            ],
        ];

        foreach ($laporan as $lap) {
            DB::table('laporan_pemakaian_barang')->updateOrInsert(
                [
                    'tanggal' => $lap['tanggal'],
                    'nama_barang' => $lap['nama_barang'],
                    'kode_unit' => $lap['kode_unit'],
                ],
                array_merge($lap, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}