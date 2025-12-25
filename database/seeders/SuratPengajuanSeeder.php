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
                'unit_name' => 'Seeders',
                'status' => 'Menunggu',
                'uraian' => 'Seeders',
                'tanggal' => now()->subDays(5),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Perbaikan',
                'status_dibaca' => false,
            ],
            [
                'no_surat_pengajuan' => '02/LOG/KCE/2025',
                'divisi_pengaju' => 'Produksi',
                'ditujukan' => 'Logistik',
                'unit_name' => 'Seeders',
                'status' => 'Disetujui',
                'uraian' => 'Seeders',
                'tanggal' => now()->subDays(4),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Permintaan',
                'status_dibaca' => true,
            ],
            [
                'no_surat_pengajuan' => '03/LOG/KCE/2025',
                'divisi_pengaju' => 'Purchasing',
                'ditujukan' => 'Logistik',
                'unit_name' => 'Seeders',
                'status' => 'Menunggu',
                'uraian' => 'Seeders',
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
                'unit_name' => 'Seeders',
                'status' => 'Menunggu',
                'uraian' => 'Seeders',
                'tanggal' => now()->subDays(4),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Pembelian',
                'status_dibaca' => false,
            ],
            [
                'no_surat_pengajuan' => '05/PUR/KCE/2025',
                'divisi_pengaju' => 'Mekanik',
                'ditujukan' => 'Purchasing',
                'unit_name' => 'Seeders',
                'status' => 'Disetujui',
                'uraian' => 'Seeders',
                'tanggal' => now()->subDays(2),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Pembelian',
                'status_dibaca' => true,
            ],
            [
                'no_surat_pengajuan' => '06/PUR/KCE/2025',
                'divisi_pengaju' => 'Produksi',
                'ditujukan' => 'Purchasing',
                'unit_name' => 'Seeders',
                'status' => 'Menunggu',
                'uraian' => 'Seeders',
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
                'unit_name' => 'Seeders',
                'status' => 'Menunggu',
                'uraian' => 'Seeders',
                'tanggal' => now()->subDays(6),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Perbaikan',
                'status_dibaca' => false,
            ],
            [
                'no_surat_pengajuan' => '08/MKN/KCE/2025',
                'divisi_pengaju' => 'Produksi',
                'ditujukan' => 'Mekanik',
                'unit_name' => 'Seeders',
                'status' => 'Disetujui',
                'uraian' => 'Seeders',
                'tanggal' => now()->subDays(3),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Perbaikan',
                'status_dibaca' => true,
            ],
            [
                'no_surat_pengajuan' => '09/MKN/KCE/2025',
                'divisi_pengaju' => 'Quality Control',
                'ditujukan' => 'Mekanik',
                'unit_name' => 'Seeders',
                'status' => 'Ditolak',
                'uraian' => 'Seeders',
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
                'unit_name' => 'Seeders',
                'status' => 'Menunggu',
                'uraian' => 'Seeders',
                'tanggal' => now()->subDay(),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Permintaan',
                'status_dibaca' => false,
            ],
            [
                'no_surat_pengajuan' => '11/PRD/KCE/2025',
                'divisi_pengaju' => 'Quality Control',
                'ditujukan' => 'Produksi',
                'unit_name' => 'Seeders',
                'status' => 'Disetujui',
                'uraian' => 'Seeders',
                'tanggal' => now()->subDays(7),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Permintaan',
                'status_dibaca' => true,
            ],
            [
                'no_surat_pengajuan' => '12/PRD/KCE/2025',
                'divisi_pengaju' => 'Plasma',
                'ditujukan' => 'Produksi',
                'unit_name' => 'Seeders',
                'status' => 'Ditolak',
                'uraian' => 'Seeders',
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
                'unit_name' => 'Seeders',
                'status' => 'Disetujui',
                'uraian' => 'Seeders',
                'tanggal' => now()->subDays(2),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Perbaikan',
                'status_dibaca' => true,
            ],
            [
                'no_surat_pengajuan' => '14/PLS/KCE/2025',
                'divisi_pengaju' => 'Produksi',
                'ditujukan' => 'Plasma',
                'unit_name' => 'Seeders',
                'status' => 'Menunggu',
                'uraian' => 'Seeders',
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
                'unit_name' => 'Seeders',
                'status' => 'Menunggu',
                'uraian' => 'Seeders',
                'tanggal' => now()->subDay(),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Permintaan',
                'status_dibaca' => false,
            ],
            [
                'no_surat_pengajuan' => '16/QC/KCE/2025',
                'divisi_pengaju' => 'Purchasing',
                'ditujukan' => 'Quality Control',
                'unit_name' => 'Seeders',
                'status' => 'Disetujui',
                'uraian' => 'Seeders',
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
                'unit_name' => 'Seeders',
                'status' => 'Disetujui',
                'uraian' => 'Seeders',
                'tanggal' => now()->subDays(8),
                'peran' => 'Admin',
                'jenis_wo' => 'Permintaan',
                'status_dibaca' => false,
            ],
            [
                'no_surat_pengajuan' => '18/ADM/KCE/2025',
                'divisi_pengaju' => 'Administrator',
                'ditujukan' => 'Logistik',
                'unit_name' => 'Seeders',
                'status' => 'Menunggu',
                'uraian' => 'Seeders',
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
                'unit_name' => 'Seeders',
                'status' => 'Menunggu',
                'uraian' => 'Seeders',
                'tanggal' => now()->subDays(13),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Perbaikan',
                'status_dibaca' => false,
            ],
            [
                'no_surat_pengajuan' => '20/MKN/KCE/2025',
                'divisi_pengaju' => 'Mekanik',
                'ditujukan' => 'Logistik',
                'unit_name' => 'Seeders',
                'status' => 'Disetujui',
                'uraian' => 'Seeders',
                'tanggal' => now()->subDays(12),
                'peran' => 'Kadiv',
                'jenis_wo' => 'Pembelian',
                'status_dibaca' => true,
            ],
            [
                'no_surat_pengajuan' => '21/PRD/KCE/2025',
                'divisi_pengaju' => 'Produksi',
                'ditujukan' => 'Logistik',
                'unit_name' => 'Seeders',
                'status' => 'Ditolak',
                'uraian' => 'Seeders',
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