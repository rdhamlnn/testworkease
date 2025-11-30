<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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
            [
                'no_surat_pengajuan' => '01/LOG/KCE/2025',
                'divisi_pengaju' => 'Mekanik',
                'ditujukan' => 'Logistik',
                'unit_name' => 'TRONTON CRANE 01',
                'status' => 'Menunggu',
                'uraian' => 'Permintaan perbaikan sistem hidrolik crane yang mengalami kebocoran pada selang utama',
                'tanggal' => now()->subDays(5),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Perbaikan',
                'status_dibaca' => false,
            ],
            [
                'no_surat_pengajuan' => '02/LOG/KCE/2025',
                'divisi_pengaju' => 'Produksi',
                'ditujukan' => 'Logistik',
                'unit_name' => 'TRONTON CRANE 02',
                'status' => 'Disetujui',
                'uraian' => 'Pengajuan WO pemeriksaan stok dan pengadaan rak gudang baru',
                'tanggal' => now()->subDays(4),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Permintaan',
                'status_dibaca' => true,
            ],
            [
                'no_surat_pengajuan' => '03/LOG/KCE/2025',
                'divisi_pengaju' => 'Purchasing',
                'ditujukan' => 'Logistik',
                'unit_name' => 'TRONTON CRANE 01',
                'status' => 'Menunggu',
                'uraian' => 'Koordinasi distribusi material hasil pembelian impor',
                'tanggal' => now()->subDays(3),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Permintaan',
                'status_dibaca' => false,
            ],
            // Work Order untuk Purchasing (Work Order Masuk)
            [
                'no_surat_pengajuan' => '04/PUR/KCE/2025',
                'divisi_pengaju' => 'Logistik',
                'ditujukan' => 'Purchasing',
                'unit_name' => 'TRONTON CRANE 02',
                'status' => 'Menunggu',
                'uraian' => 'Pengajuan WO pemeriksaan stok dan pengadaan rak gudang baru',
                'tanggal' => now()->subDays(4),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Pembelian',
                'status_dibaca' => false,
            ],
            [
                'no_surat_pengajuan' => '05/PUR/KCE/2025',
                'divisi_pengaju' => 'Mekanik',
                'ditujukan' => 'Purchasing',
                'unit_name' => 'TRONTON CRANE 01',
                'status' => 'Disetujui',
                'uraian' => 'Permintaan pembelian suku cadang untuk maintenance Q1',
                'tanggal' => now()->subDays(2),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Pembelian',
                'status_dibaca' => true,
            ],
            [
                'no_surat_pengajuan' => '06/PUR/KCE/2025',
                'divisi_pengaju' => 'Produksi',
                'ditujukan' => 'Purchasing',
                'unit_name' => 'TRONTON CRANE 02',
                'status' => 'Menunggu',
                'uraian' => 'Pengadaan bahan baku tambahan untuk produksi batch 21',
                'tanggal' => now()->subDay(),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Pembelian',
                'status_dibaca' => false,
            ],
            // Work Order untuk Mekanik (Work Order Masuk)
            [
                'no_surat_pengajuan' => '07/MKN/KCE/2025',
                'divisi_pengaju' => 'Plasma',
                'ditujukan' => 'Mekanik',
                'unit_name' => 'TRONTON CRANE 01',
                'status' => 'Menunggu',
                'uraian' => 'Permintaan bantuan perbaikan nozzle pemotong plasma',
                'tanggal' => now()->subDays(6),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Perbaikan',
                'status_dibaca' => false,
            ],
            [
                'no_surat_pengajuan' => '08/MKN/KCE/2025',
                'divisi_pengaju' => 'Produksi',
                'ditujukan' => 'Mekanik',
                'unit_name' => 'TRONTON CRANE 02',
                'status' => 'Disetujui',
                'uraian' => 'Maintenance rutin sistem pengereman dan penggantian kampas rem yang sudah aus',
                'tanggal' => now()->subDays(3),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Perbaikan',
                'status_dibaca' => true,
            ],
            [
                'no_surat_pengajuan' => '09/MKN/KCE/2025',
                'divisi_pengaju' => 'Quality Control',
                'ditujukan' => 'Mekanik',
                'unit_name' => 'TRONTON CRANE 01',
                'status' => 'Ditolak',
                'uraian' => 'Permintaan kalibrasi alat ukur mekanik',
                'tanggal' => now()->subDays(7),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Permintaan',
                'status_dibaca' => false,
            ],
            // Work Order untuk Produksi (Work Order Masuk)
            [
                'no_surat_pengajuan' => '10/PRD/KCE/2025',
                'divisi_pengaju' => 'Mekanik',
                'ditujukan' => 'Produksi',
                'unit_name' => 'TRONTON CRANE 01',
                'status' => 'Menunggu',
                'uraian' => 'Pengujian kualitas output pasca perbaikan dan kalibrasi mesin produksi',
                'tanggal' => now()->subDay(),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Permintaan',
                'status_dibaca' => false,
            ],
            [
                'no_surat_pengajuan' => '11/PRD/KCE/2025',
                'divisi_pengaju' => 'Quality Control',
                'ditujukan' => 'Produksi',
                'unit_name' => 'TRONTON CRANE 02',
                'status' => 'Disetujui',
                'uraian' => 'WO kalibrasi ulang alat ukur produksi pasca audit internal',
                'tanggal' => now()->subDays(7),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Permintaan',
                'status_dibaca' => true,
            ],
            [
                'no_surat_pengajuan' => '12/PRD/KCE/2025',
                'divisi_pengaju' => 'Plasma',
                'ditujukan' => 'Produksi',
                'unit_name' => 'TRONTON CRANE 01',
                'status' => 'Ditolak',
                'uraian' => 'Request dukungan pemotongan material tebal untuk proyek batch 21',
                'tanggal' => now()->subDays(9),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Permintaan',
                'status_dibaca' => false,
            ],
            // Work Order untuk Plasma (Work Order Masuk)
            [
                'no_surat_pengajuan' => '13/PLS/KCE/2025',
                'divisi_pengaju' => 'Mekanik',
                'ditujukan' => 'Plasma',
                'unit_name' => 'TRONTON CRANE 02',
                'status' => 'Disetujui',
                'uraian' => 'Pengecekan sistem hidrolik tambahan untuk unit plasma',
                'tanggal' => now()->subDays(2),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Perbaikan',
                'status_dibaca' => true,
            ],
            [
                'no_surat_pengajuan' => '14/PLS/KCE/2025',
                'divisi_pengaju' => 'Produksi',
                'ditujukan' => 'Plasma',
                'unit_name' => 'TRONTON CRANE 01',
                'status' => 'Menunggu',
                'uraian' => 'Permintaan pemotongan material untuk proyek khusus',
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
                'unit_name' => 'TRONTON CRANE 01',
                'status' => 'Menunggu',
                'uraian' => 'Pengujian kualitas output pasca perbaikan dan kalibrasi mesin produksi',
                'tanggal' => now()->subDay(),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Permintaan',
                'status_dibaca' => false,
            ],
            [
                'no_surat_pengajuan' => '16/QC/KCE/2025',
                'divisi_pengaju' => 'Purchasing',
                'ditujukan' => 'Quality Control',
                'unit_name' => 'TRONTON CRANE 02',
                'status' => 'Disetujui',
                'uraian' => 'Koordinasi inspeksi barang yang baru dibeli untuk memastikan kualitas',
                'tanggal' => now()->subDays(11),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Permintaan',
                'status_dibaca' => true,
            ],
            // Work Order untuk Admin (semua divisi)
            [
                'no_surat_pengajuan' => '17/ADM/KCE/2025',
                'divisi_pengaju' => 'Administrator',
                'ditujukan' => 'Mekanik',
                'unit_name' => 'TRONTON CRANE 01',
                'status' => 'Disetujui',
                'uraian' => 'Permintaan evaluasi sistem WorkEase terkait integrasi data',
                'tanggal' => now()->subDays(8),
                'peran' => 'Admin',
                'jenis_wo' => 'Permintaan',
                'status_dibaca' => false,
            ],
            [
                'no_surat_pengajuan' => '18/ADM/KCE/2025',
                'divisi_pengaju' => 'Administrator',
                'ditujukan' => 'Logistik',
                'unit_name' => 'TRONTON CRANE 02',
                'status' => 'Menunggu',
                'uraian' => 'Koordinasi update sistem inventory dan tracking',
                'tanggal' => now()->subDays(10),
                'peran' => 'Admin',
                'jenis_wo' => 'Permintaan',
                'status_dibaca' => false,
            ],
            // Work Order untuk Riwayat (berbagai status)
            [
                'no_surat_pengajuan' => '19/LOG/KCE/2025',
                'divisi_pengaju' => 'Logistik',
                'ditujukan' => 'Purchasing',
                'unit_name' => 'TRONTON CRANE 01',
                'status' => 'Menunggu',
                'uraian' => 'Penjadwalan perawatan forklift gudang pusat',
                'tanggal' => now()->subDays(13),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Perbaikan',
                'status_dibaca' => false,
            ],
            [
                'no_surat_pengajuan' => '20/MKN/KCE/2025',
                'divisi_pengaju' => 'Mekanik',
                'ditujukan' => 'Logistik',
                'unit_name' => 'TRONTON CRANE 02',
                'status' => 'Disetujui',
                'uraian' => 'Pengadaan suku cadang tambahan untuk jadwal maintenance Q1',
                'tanggal' => now()->subDays(12),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Pembelian',
                'status_dibaca' => true,
            ],
            [
                'no_surat_pengajuan' => '21/PRD/KCE/2025',
                'divisi_pengaju' => 'Produksi',
                'ditujukan' => 'Logistik',
                'unit_name' => 'TRONTON CRANE 01',
                'status' => 'Ditolak',
                'uraian' => 'Permintaan penjadwalan pengiriman bahan baku tambahan',
                'tanggal' => now()->subDays(15),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Permintaan',
                'status_dibaca' => false,
            ],
        ];

        $resolveAccountId = function (?int $divisiId) use ($akunByDivisi, $defaultAkunId) {
            if (!$divisiId) {
                return $defaultAkunId;
            }

            $accounts = $akunByDivisi->get($divisiId);
            if ($accounts && $accounts->isNotEmpty()) {
                return $accounts->first()->id_akun;
            }

            return $defaultAkunId;
        };

        foreach ($entries as $entry) {
            $divisiPengajuId = $divisiMap->get($entry['divisi_pengaju']) ?? $defaultDivisiId;
            $peranId = $peranMap->get($entry['peran'] ?? 'Kadiv') ?? $defaultPeranId;
            $unitId = $unitMap->get($entry['unit_name']) ?? $defaultUnitId;
            $idAkun = $resolveAccountId($divisiPengajuId);
            $status = $entry['status'] ?? 'Menunggu';
            $idVerifikator = $statusVerifikator[$status] ?? $statusVerifikator['Menunggu'];
            $idJenisWO = $jenisWOMap->get($entry['jenis_wo'] ?? 'Permintaan') ?? $defaultJenisWOId;
            $statusDibaca = $entry['status_dibaca'] ?? false;

            DB::table('surat_pengajuan')->updateOrInsert(
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
                    'status_dibaca' => $statusDibaca,
                    'created_at' => $entry['tanggal'],
                    'updated_at' => $entry['tanggal'],
                ]
            );
        }
    }
}