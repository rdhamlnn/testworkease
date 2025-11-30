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
        $unit = DB::table('unit')->first();

        $akunId = $akun->id_akun ?? 1;
        $divisiId = $divisi->id_divisi ?? 1;
        $unitId = $unit->id_unit ?? 1;

        $laporan = [
            [
                'tanggal' => now()->subDay()->toDateString(),
                'nama_barang' => 'Selang Hidrolik 3/8 inch',
                'kode_unit' => 'KCE T01',
                'jumlah' => 3,
                'bentuk_satuan' => 'Meter',
                'harga_satuan' => 45000,
                'total_harga' => 135000,
                'keterangan' => 'Penggantian selang utama crane',
                'id_akun' => $akunId,
                'id_divisi' => $divisiId,
                'id_unit' => $unitId,
            ],
            [
                'tanggal' => now()->subDays(2)->toDateString(),
                'nama_barang' => 'Kampas Rem Depan',
                'kode_unit' => 'KCE DT01',
                'jumlah' => 4,
                'bentuk_satuan' => 'Set',
                'harga_satuan' => 85000,
                'total_harga' => 340000,
                'keterangan' => 'Perbaikan sistem pengereman dump truck',
                'id_akun' => $akunId,
                'id_divisi' => $divisiId,
                'id_unit' => $unitId,
            ],
            [
                'tanggal' => now()->subDays(3)->toDateString(),
                'nama_barang' => 'Filter Solar',
                'kode_unit' => 'KCE T02',
                'jumlah' => 6,
                'bentuk_satuan' => 'Pcs',
                'harga_satuan' => 65000,
                'total_harga' => 390000,
                'keterangan' => 'Servis berkala 500 jam',
                'id_akun' => $akunId,
                'id_divisi' => $divisiId,
                'id_unit' => $unitId,
            ],
            [
                'tanggal' => now()->subDays(4)->toDateString(),
                'nama_barang' => 'Grease EP2',
                'kode_unit' => 'KCE FL01',
                'jumlah' => 10,
                'bentuk_satuan' => 'Tube',
                'harga_satuan' => 42000,
                'total_harga' => 420000,
                'keterangan' => 'Lubrikasi bearing wheel loader',
                'id_akun' => $akunId,
                'id_divisi' => $divisiId,
                'id_unit' => $unitId,
            ],
            [
                'tanggal' => now()->subDays(5)->toDateString(),
                'nama_barang' => 'Lampu LED 24V',
                'kode_unit' => 'KCE LT01',
                'jumlah' => 8,
                'bentuk_satuan' => 'Pcs',
                'harga_satuan' => 115000,
                'total_harga' => 920000,
                'keterangan' => 'Penggantian lampu hazard trailer',
                'id_akun' => $akunId,
                'id_divisi' => $divisiId,
                'id_unit' => $unitId,
            ],
            [
                'tanggal' => now()->subDays(6)->toDateString(),
                'nama_barang' => 'Oil Filter C7',
                'kode_unit' => 'KCE EX01',
                'jumlah' => 2,
                'bentuk_satuan' => 'Pcs',
                'harga_satuan' => 175000,
                'total_harga' => 350000,
                'keterangan' => 'Maintenance excavator 320D',
                'id_akun' => $akunId,
                'id_divisi' => $divisiId,
                'id_unit' => $unitId,
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