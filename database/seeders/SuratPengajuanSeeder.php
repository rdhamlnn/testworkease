<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\SuratPengajuan;
use App\Models\Akun;
use App\Models\Divisi;
use App\Models\Peran;
use App\Models\Unit;
use App\Models\JenisWorkOrder;

class SuratPengajuanSeeder extends Seeder
{
    public function run(): void
    {
        $divisiMap = DB::table('divisi')->pluck('id_divisi', 'nama_divisi');
        $peranMap = DB::table('peran')->pluck('id_peran', 'nama_peran');
        $unitMap = DB::table('unit')->pluck('id_unit', 'nama_unit');
        $jenisWOMap = DB::table('jenis_work_order')->pluck('id_jenis_wo', 'nama_jenis_wo');
        $akunByDivisi = DB::table('akun')
            ->select('id_akun', 'id_divisi')
            ->orderBy('id_akun')
            ->get()
            ->groupBy('id_divisi');

        // Get unit names from master
        $unitNames = DB::table('unit')->pluck('nama_unit')->toArray();
        $unit1 = $unitNames[0] ?? 'TRONTON CRANE 01';
        $unit2 = $unitNames[1] ?? 'TRONTON CRANE 02';

        // Get barang names from master for Pembelian format
        $barangNames = DB::table('daftar_barang')->pluck('nama_barang')->toArray();
        $barang1 = $barangNames[0] ?? 'Selang Hidrolik';
        $barang2 = $barangNames[1] ?? 'O-Ring Set';
        $barang3 = $barangNames[2] ?? 'Kampas Rem';
        $barang4 = $barangNames[3] ?? 'Minyak Rem DOT 4';
        $barang5 = $barangNames[4] ?? 'Filter Oli';

        $defaultDivisiId = $divisiMap->first() ?? 1;
        $defaultPeranId = $peranMap->first() ?? 1;
        $defaultUnitId = $unitMap->first() ?? 1;
        $defaultJenisWOId = $jenisWOMap->get('Permintaan') ?? $jenisWOMap->first() ?? 1;
        $defaultAkunId = DB::table('akun')->orderBy('id_akun')->value('id_akun') ?? 1;

        $statusVerifikator = [
            'Menunggu' => 1,
            'Disetujui' => 2,
            'Ditolak' => 3,
        ];

        $entries = [
            // Work Order untuk Logistik (Work Order Masuk)
            // Perbaikan: ditujukan ke Mekanik (bukan Logistik) - perbaiki alur
            [
                'no_surat_pengajuan' => '01/LOG/KCE/2025',
                'divisi_pengaju' => 'Logistik',
                'ditujukan' => 'Mekanik',
                'unit_name' => $unit1, // Perbaikan: nama unit dari master
                'status' => 'Menunggu',
                'uraian' => '[seeders] Perbaikan sistem hidrolik crane',
                'tanggal' => now()->subDays(5),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Perbaikan',
                'status_dibaca' => false,
            ],
            // Permintaan: unit = "-"
            [
                'no_surat_pengajuan' => '02/LOG/KCE/2025',
                'divisi_pengaju' => 'Produksi',
                'ditujukan' => 'Logistik',
                'unit_name' => '-', // Permintaan: hanya uraian
                'status' => 'Disetujui',
                'uraian' => '[seeders] Permintaan jasa pengiriman material',
                'tanggal' => now()->subDays(4),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Permintaan',
                'status_dibaca' => true,
            ],
            [
                'no_surat_pengajuan' => '03/LOG/KCE/2025',
                'divisi_pengaju' => 'Purchasing',
                'ditujukan' => 'Logistik',
                'unit_name' => '-', // Permintaan: hanya uraian
                'status' => 'Menunggu',
                'uraian' => '[seeders] Permintaan pengecekan stok gudang',
                'tanggal' => now()->subDays(3),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Permintaan',
                'status_dibaca' => false,
            ],
            // Work Order untuk Purchasing (Work Order Masuk) - Pembelian
            [
                'no_surat_pengajuan' => '04/PUR/KCE/2025',
                'divisi_pengaju' => 'Logistik',
                'ditujukan' => 'Purchasing',
                'unit_name' => "{$barang1} (qty: 2), {$barang2} (qty: 3)", // Pembelian: format barang
                'status' => 'Menunggu',
                'uraian' => '[seeders] Pembelian sparepart untuk maintenance rutin',
                'tanggal' => now()->subDays(4),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Pembelian',
                'status_dibaca' => false,
            ],
            [
                'no_surat_pengajuan' => '05/PUR/KCE/2025',
                'divisi_pengaju' => 'Mekanik',
                'ditujukan' => 'Logistik',
                'unit_name' => "{$barang3} (qty: 4), {$barang4} (qty: 2)", // Pembelian: format barang
                'status' => 'Disetujui',
                'uraian' => '[seeders] Pembelian bahan untuk perbaikan rem tronton',
                'tanggal' => now()->subDays(2),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Pembelian',
                'status_dibaca' => true,
            ],
            [
                'no_surat_pengajuan' => '06/PUR/KCE/2025',
                'divisi_pengaju' => 'Produksi',
                'ditujukan' => 'Logistik',
                'unit_name' => "{$barang5} (qty: 5)", // Pembelian: format barang
                'status' => 'Menunggu',
                'uraian' => '[seeders] Pembelian filter untuk service berkala',
                'tanggal' => now()->subDay(),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Pembelian',
                'status_dibaca' => false,
            ],
            // Work Order untuk Mekanik (Work Order Masuk) - Perbaikan
            [
                'no_surat_pengajuan' => '07/MKN/KCE/2025',
                'divisi_pengaju' => 'Plasma',
                'ditujukan' => 'Mekanik',
                'unit_name' => $unit1, // Perbaikan: nama unit dari master
                'status' => 'Menunggu',
                'uraian' => '[seeders] Perbaikan lampu hazard tidak menyala',
                'tanggal' => now()->subDays(6),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Perbaikan',
                'status_dibaca' => false,
            ],
            [
                'no_surat_pengajuan' => '08/MKN/KCE/2025',
                'divisi_pengaju' => 'Produksi',
                'ditujukan' => 'Mekanik',
                'unit_name' => $unit2, // Perbaikan: nama unit dari master
                'status' => 'Disetujui',
                'uraian' => '[seeders] Perbaikan oli bocor di mesin',
                'tanggal' => now()->subDays(3),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Perbaikan',
                'status_dibaca' => true,
            ],
            // Permintaan ditujukan ke mekanik - ini tidak valid sesuai aturan, ubah ke Permintaan ke divisi lain
            [
                'no_surat_pengajuan' => '09/MKN/KCE/2025',
                'divisi_pengaju' => 'Quality Control',
                'ditujukan' => 'Produksi',
                'unit_name' => '-', // Permintaan: hanya uraian
                'status' => 'Ditolak',
                'uraian' => '[seeders] Permintaan bantuan tenaga kerja',
                'tanggal' => now()->subDays(7),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Permintaan',
                'status_dibaca' => false,
            ],
            // Work Order untuk Produksi (Work Order Masuk) - Permintaan
            [
                'no_surat_pengajuan' => '10/PRD/KCE/2025',
                'divisi_pengaju' => 'Mekanik',
                'ditujukan' => 'Produksi',
                'unit_name' => '-', // Permintaan: hanya uraian
                'status' => 'Menunggu',
                'uraian' => '[seeders] Permintaan koordinasi jadwal produksi',
                'tanggal' => now()->subDay(),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Permintaan',
                'status_dibaca' => false,
            ],
            [
                'no_surat_pengajuan' => '11/PRD/KCE/2025',
                'divisi_pengaju' => 'Quality Control',
                'ditujukan' => 'Produksi',
                'unit_name' => '-', // Permintaan: hanya uraian
                'status' => 'Disetujui',
                'uraian' => '[seeders] Permintaan laporan hasil produksi',
                'tanggal' => now()->subDays(7),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Permintaan',
                'status_dibaca' => true,
            ],
            [
                'no_surat_pengajuan' => '12/PRD/KCE/2025',
                'divisi_pengaju' => 'Plasma',
                'ditujukan' => 'Produksi',
                'unit_name' => '-', // Permintaan: hanya uraian
                'status' => 'Ditolak',
                'uraian' => '[seeders] Permintaan tambahan shift kerja',
                'tanggal' => now()->subDays(9),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Permintaan',
                'status_dibaca' => false,
            ],
            // Work Order untuk Plasma (Work Order Masuk)
            [
                'no_surat_pengajuan' => '13/PLS/KCE/2025',
                'divisi_pengaju' => 'Produksi',
                'ditujukan' => 'Mekanik',
                'unit_name' => $unit1, // Perbaikan: nama unit dari master
                'status' => 'Disetujui',
                'uraian' => '[seeders] Perbaikan mesin plasma cutting',
                'tanggal' => now()->subDays(2),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Perbaikan',
                'status_dibaca' => true,
            ],
            [
                'no_surat_pengajuan' => '14/PLS/KCE/2025',
                'divisi_pengaju' => 'Produksi',
                'ditujukan' => 'Plasma',
                'unit_name' => '-', // Permintaan: hanya uraian
                'status' => 'Menunggu',
                'uraian' => '[seeders] Permintaan pemotongan plat baja',
                'tanggal' => now()->subDays(5),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Permintaan',
                'status_dibaca' => false,
            ],
            // Work Order untuk Quality Control (Work Order Masuk)
            [
                'no_surat_pengajuan' => '15/QC/KCE/2025',
                'divisi_pengaju' => 'Produksi',
                'ditujukan' => 'Quality Control',
                'unit_name' => '-', // Permintaan: hanya uraian
                'status' => 'Menunggu',
                'uraian' => '[seeders] Permintaan inspeksi kualitas hasil welding',
                'tanggal' => now()->subDay(),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Permintaan',
                'status_dibaca' => false,
            ],
            [
                'no_surat_pengajuan' => '16/QC/KCE/2025',
                'divisi_pengaju' => 'Purchasing',
                'ditujukan' => 'Quality Control',
                'unit_name' => '-', // Permintaan: hanya uraian
                'status' => 'Disetujui',
                'uraian' => '[seeders] Permintaan verifikasi spesifikasi barang',
                'tanggal' => now()->subDays(11),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Permintaan',
                'status_dibaca' => true,
            ],
            // Work Order untuk Riwayat (berbagai status)
            [
                'no_surat_pengajuan' => '17/LOG/KCE/2025',
                'divisi_pengaju' => 'Logistik',
                'ditujukan' => 'Mekanik',
                'unit_name' => $unit2, // Perbaikan: nama unit dari master
                'status' => 'Menunggu',
                'uraian' => '[seeders] Perbaikan sensor suhu mesin',
                'tanggal' => now()->subDays(13),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Perbaikan',
                'status_dibaca' => false,
            ],
            [
                'no_surat_pengajuan' => '18/MKN/KCE/2025',
                'divisi_pengaju' => 'Mekanik',
                'ditujukan' => 'Logistik',
                'unit_name' => "{$barang1} (qty: 3), {$barang3} (qty: 2)", // Pembelian: format barang
                'status' => 'Disetujui',
                'uraian' => '[seeders] Pembelian sparepart untuk stok gudang',
                'tanggal' => now()->subDays(12),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Pembelian',
                'status_dibaca' => true,
            ],
            [
                'no_surat_pengajuan' => '19/PRD/KCE/2025',
                'divisi_pengaju' => 'Produksi',
                'ditujukan' => 'Logistik',
                'unit_name' => '-', // Permintaan: hanya uraian
                'status' => 'Ditolak',
                'uraian' => '[seeders] Permintaan pengiriman material urgent',
                'tanggal' => now()->subDays(15),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Permintaan',
                'status_dibaca' => false,
            ],
        ];

        $resolveAccountId = function (int $divisiId) use ($akunByDivisi, $defaultAkunId) {
            $accounts = $akunByDivisi->get($divisiId);
            return ($accounts && $accounts->isNotEmpty()) ? $accounts->first()->id_akun : $defaultAkunId;
        };

        foreach ($entries as $entry) {
            $divisiPengajuId = $divisiMap->get($entry['divisi_pengaju']) ?? $defaultDivisiId;
            $peranId = $peranMap->get($entry['peran'] ?? 'Kadiv') ?? $defaultPeranId;
            $unitId = $unitMap->get($entry['unit_name']) ?? $defaultUnitId;
            $idAkun = $resolveAccountId($divisiPengajuId);
            $idJenisWO = $jenisWOMap->get($entry['jenis_wo'] ?? 'Permintaan') ?? $defaultJenisWOId;
            $status = $entry['status'] ?? 'Menunggu';
            $idVerifikator = $statusVerifikator[$status] ?? 1;

            SuratPengajuan::updateOrCreate(
                ['no_surat_pengajuan' => $entry['no_surat_pengajuan']],
                [
                    'ditujukan' => $entry['ditujukan'],
                    'tanggal' => $entry['tanggal']->toDateString(),
                    'divisi_pengaju' => $entry['divisi_pengaju'],
                    'unit' => $entry['unit_name'],
                    'uraian' => $entry['uraian'],
                    'dokumentasi' => $entry['dokumentasi'] ?? '-',
                    'status' => $status,
                    'id_divisi' => $divisiPengajuId,
                    'id_peran' => $peranId,
                    'id_verifikator' => $idVerifikator,
                    'id_akun' => $idAkun,
                    'id_unit' => $unitId,
                    'id_jenis_wo' => $idJenisWO,
                    'status_dibaca' => $entry['status_dibaca'] ?? false,
                    'created_at' => $entry['tanggal'],
                    'updated_at' => $entry['tanggal'],
                ]
            );
        }
    }
}