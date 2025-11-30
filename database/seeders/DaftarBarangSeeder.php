<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DaftarBarangSeeder extends Seeder
{
    public function run(): void
    {
        // Data master stok barang (tabel daftar_barang)
        $masterBarang = [
            ['nama_barang' => 'Selang Hidrolik', 'satuan' => 'pcs', 'stok' => 10, 'harga_barang' => 450000, 'path_foto' => null],
            ['nama_barang' => 'O-Ring Set', 'satuan' => 'set', 'stok' => 15, 'harga_barang' => 150000, 'path_foto' => null],
            ['nama_barang' => 'Kampas Rem', 'satuan' => 'pcs', 'stok' => 20, 'harga_barang' => 250000, 'path_foto' => null],
            ['nama_barang' => 'Minyak Rem DOT 4', 'satuan' => 'botol', 'stok' => 30, 'harga_barang' => 85000, 'path_foto' => null],
            ['nama_barang' => 'Filter Oli', 'satuan' => 'pcs', 'stok' => 25, 'harga_barang' => 120000, 'path_foto' => null],
            ['nama_barang' => 'Seal Kit', 'satuan' => 'set', 'stok' => 8, 'harga_barang' => 550000, 'path_foto' => null],
            ['nama_barang' => 'Grease', 'satuan' => 'tube', 'stok' => 40, 'harga_barang' => 45000, 'path_foto' => null],
            ['nama_barang' => 'Belt Alternator', 'satuan' => 'pcs', 'stok' => 12, 'harga_barang' => 175000, 'path_foto' => null],
            ['nama_barang' => 'Bearing Set', 'satuan' => 'set', 'stok' => 18, 'harga_barang' => 350000, 'path_foto' => null],
            ['nama_barang' => 'Piston Kit', 'satuan' => 'set', 'stok' => 5, 'harga_barang' => 850000, 'path_foto' => null],
            ['nama_barang' => 'Komponen Premium', 'satuan' => 'pcs', 'stok' => 3, 'harga_barang' => 2000000, 'path_foto' => null],
            ['nama_barang' => 'Radiator', 'satuan' => 'pcs', 'stok' => 6, 'harga_barang' => 1200000, 'path_foto' => null],
            ['nama_barang' => 'Timing Belt', 'satuan' => 'pcs', 'stok' => 9, 'harga_barang' => 450000, 'path_foto' => null],
            ['nama_barang' => 'Water Pump', 'satuan' => 'pcs', 'stok' => 7, 'harga_barang' => 650000, 'path_foto' => null],
            ['nama_barang' => 'Oil Filter', 'satuan' => 'pcs', 'stok' => 35, 'harga_barang' => 95000, 'path_foto' => null],
        ];

        // Seed master stok barang
        foreach ($masterBarang as $barang) {
            DB::table('daftar_barang')->updateOrInsert(
                [
                    'nama_barang' => $barang['nama_barang'],
                    'satuan' => $barang['satuan'],
                ],
                [
                    'stok' => $barang['stok'],
                    'harga_barang' => $barang['harga_barang'],
                    'path_foto' => $barang['path_foto'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        // Mapping no_permintaan_barang ke data detail barang (untuk detail permintaan)
        $detailMap = [
            '001/LOG/KCE/' . now()->year => [
                ['nama_barang' => 'Selang Hidrolik', 'jumlah' => 2, 'satuan' => 'pcs', 'estimasi_harga' => 450000],
                ['nama_barang' => 'O-Ring Set', 'jumlah' => 1, 'satuan' => 'set', 'estimasi_harga' => 150000],
            ],
            '002/LOG/KCE/' . now()->year => [
                ['nama_barang' => 'Kampas Rem', 'jumlah' => 4, 'satuan' => 'pcs', 'estimasi_harga' => 250000],
            ],
            '003/LOG/KCE/' . now()->year => [
                ['nama_barang' => 'Minyak Rem DOT 4', 'jumlah' => 2, 'satuan' => 'botol', 'estimasi_harga' => 85000],
            ],
            '004/LOG/KCE/' . now()->year => [
                ['nama_barang' => 'Filter Oli', 'jumlah' => 3, 'satuan' => 'pcs', 'estimasi_harga' => 120000],
            ],
            '005/LOG/KCE/' . now()->year => [
                ['nama_barang' => 'Seal Kit', 'jumlah' => 1, 'satuan' => 'set', 'estimasi_harga' => 550000],
            ],
            '006/LOG/KCE/' . now()->year => [
                ['nama_barang' => 'Grease', 'jumlah' => 5, 'satuan' => 'tube', 'estimasi_harga' => 45000],
            ],
            '007/LOG/KCE/' . now()->year => [
                ['nama_barang' => 'Belt Alternator', 'jumlah' => 1, 'satuan' => 'pcs', 'estimasi_harga' => 175000],
            ],
            '008/LOG/KCE/' . now()->year => [
                ['nama_barang' => 'Bearing Set', 'jumlah' => 2, 'satuan' => 'set', 'estimasi_harga' => 350000],
            ],
            '009/LOG/KCE/' . now()->year => [
                ['nama_barang' => 'Piston Kit', 'jumlah' => 1, 'satuan' => 'set', 'estimasi_harga' => 850000],
            ],
            '010/LOG/KCE/' . now()->year => [
                ['nama_barang' => 'Komponen Premium', 'jumlah' => 1, 'satuan' => 'pcs', 'estimasi_harga' => 2000000],
            ],
            '011/LOG/KCE/' . now()->year => [
                ['nama_barang' => 'Radiator', 'jumlah' => 1, 'satuan' => 'pcs', 'estimasi_harga' => 1200000],
            ],
            '012/LOG/KCE/' . now()->year => [
                ['nama_barang' => 'Timing Belt', 'jumlah' => 1, 'satuan' => 'pcs', 'estimasi_harga' => 450000],
            ],
            '013/LOG/KCE/' . now()->year => [
                ['nama_barang' => 'Water Pump', 'jumlah' => 1, 'satuan' => 'pcs', 'estimasi_harga' => 650000],
            ],
            '014/LOG/KCE/' . now()->year => [
                ['nama_barang' => 'Oil Filter', 'jumlah' => 3, 'satuan' => 'pcs', 'estimasi_harga' => 95000],
            ],
        ];

        // Seed detail permintaan (many-to-many)
        foreach ($detailMap as $noPermintaan => $items) {
            $permintaan = DB::table('permintaan_barang')
                ->where('no_permintaan_barang', $noPermintaan)
                ->first();

            if (!$permintaan) {
                continue;
            }

            foreach ($items as $item) {
                // Cari master stok barang
                $master = DB::table('daftar_barang')
                    ->where('nama_barang', $item['nama_barang'])
                    ->where('satuan', $item['satuan'])
                    ->first();

                if (!$master) {
                    continue; // Skip jika master tidak ada
                }

                // Detail permintaan (tabel detail_barang_permintaan)
                DB::table('detail_barang_permintaan')->updateOrInsert(
                    [
                        'id_permintaan_barang' => $permintaan->id_permintaan_barang,
                        'nama_barang' => $item['nama_barang'],
                    ],
                    [
                        'id_daftar_barang_master' => $master->id_daftar_barang,
                        'jumlah' => $item['jumlah'],
                        'satuan' => $item['satuan'],
                        'estimasi_harga' => $item['estimasi_harga'],
                        'updated_at' => now(),
                        'created_at' => $permintaan->created_at ?? now(),
                    ]
                );
            }
        }
    }
}


