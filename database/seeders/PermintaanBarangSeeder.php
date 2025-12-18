<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\PermintaanBarang;
use App\Models\SuratPengajuan;
use App\Models\Akun;

class PermintaanBarangSeeder extends Seeder
{
    public function run(): void
    {
        // Mapping status string ke id_status_wo dari tabel master
        $statusMap = DB::table('status_wo')
            ->pluck('id_status_wo', 'nama_status')
            ->toArray();

        $getStatusId = function (string $nama) use ($statusMap) {
            return $statusMap[$nama] ?? null;
        };

        // Ambil referensi data yang dibutuhkan
        $suratAdm = DB::table('surat_pengajuan')->where('no_surat_pengajuan', '17/ADM/KCE/2025')->first();
        $suratMkn = DB::table('surat_pengajuan')->where('no_surat_pengajuan', '08/MKN/KCE/2025')->first();
        $suratLog = DB::table('surat_pengajuan')->where('no_surat_pengajuan', '04/PUR/KCE/2025')->first();
        
        // Akun-akun peran terkait
        $logistik = DB::table('akun')->where('email', 'logistik@kce.com')->first();
        $purchasing = DB::table('akun')->where('email', 'purchasing@kce.com')->first();
        $atasan = DB::table('akun')->where('email', 'atasan@kce.com')->first();

        // Fallback jika belum ada
        $suratAdmId = $suratAdm->id_surat_pengajuan ?? DB::table('surat_pengajuan')->orderBy('id_surat_pengajuan')->value('id_surat_pengajuan') ?? 1;
        $suratMknId = $suratMkn->id_surat_pengajuan ?? DB::table('surat_pengajuan')->orderBy('id_surat_pengajuan')->value('id_surat_pengajuan') ?? 1;
        $suratLogId = $suratLog->id_surat_pengajuan ?? DB::table('surat_pengajuan')->orderBy('id_surat_pengajuan')->value('id_surat_pengajuan') ?? 1;
        $logistikId = $logistik->id_akun ?? DB::table('akun')->where('email', 'logistik@kce.com')->value('id_akun') ?? 1;
        $purchasingId = $purchasing->id_akun ?? DB::table('akun')->where('email', 'purchasing@kce.com')->value('id_akun') ?? 1;
        $atasanId = $atasan->id_akun ?? DB::table('akun')->where('email', 'atasan@kce.com')->value('id_akun') ?? 1;

        // Seed contoh permintaan barang - 001
        PermintaanBarang::updateOrCreate(
            ['no_permintaan_barang' => '001/LOG/KCE/' . now()->year],
            [
                'id_surat_pengajuan' => $suratAdmId,
                'tanggal_permintaan' => now()->subDays(3)->toDateString(),
                'id_status_wo' => $getStatusId('Menunggu Purchasing'),
                'status' => 'Menunggu Purchasing',
                'total_estimasi_harga' => 600000,
                'catatan_logistik' => 'Urgent untuk perbaikan kebocoran',
                'id_logistik' => $logistikId,
                'id_akun' => $logistikId,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ]
        );

        // Seed contoh permintaan barang - 002
        PermintaanBarang::updateOrCreate(
            ['no_permintaan_barang' => '002/LOG/KCE/' . now()->year],
            [
                'id_surat_pengajuan' => $suratMknId,
                'tanggal_permintaan' => now()->subDays(2)->toDateString(),
                'id_status_wo' => $getStatusId('Menunggu Approval Atasan'),
                'status' => 'Menunggu Approval Atasan',
                'total_estimasi_harga' => 1000000,
                'catatan_logistik' => 'Butuh segera karena aus',
                'catatan_purchasing' => 'Menunggu approval atasan',
                'id_logistik' => $logistikId,
                'id_purchasing' => $purchasingId,
                'id_akun' => $logistikId,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ]
        );

        // Seed contoh permintaan barang - 003
        PermintaanBarang::updateOrCreate(
            ['no_permintaan_barang' => '003/LOG/KCE/' . now()->year],
            [
                'id_surat_pengajuan' => $suratAdmId,
                'tanggal_permintaan' => now()->subDay()->toDateString(),
                'id_status_wo' => $getStatusId('Dikirim Purchasing'),
                'status' => 'Dikirim Purchasing',
                'total_estimasi_harga' => 170000,
                'catatan_logistik' => 'Untuk maintenance pengereman',
                'catatan_purchasing' => 'Sudah dibeli dan dikirim',
                'catatan_atasan' => 'Disetujui',
                'id_logistik' => $logistikId,
                'id_purchasing' => $purchasingId,
                'id_atasan' => $atasanId,
                'id_akun' => $logistikId,
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ]
        );

        // Tambahan data flow lainnya
        $additionalEntries = [
            [
                'no' => '004',
                'surat_id' => $suratAdmId,
                'status' => 'Disetujui Atasan',
                'harga' => 360000,
                'catatan' => 'Disetujui untuk perawatan berkala',
                'atasan_id' => $atasanId
            ],
            [
                'no' => '005',
                'surat_id' => $suratMknId,
                'status' => 'Dibeli Purchasing',
                'harga' => 550000,
                'catatan' => 'Pembelian selesai',
                'atasan_id' => $atasanId,
                'purchasing_id' => $purchasingId
            ],
            [
                'no' => '006',
                'surat_id' => $suratAdmId,
                'status' => 'Diterima Logistik',
                'harga' => 225000,
                'catatan' => 'Barang diterima lengkap',
                'atasan_id' => $atasanId,
                'purchasing_id' => $purchasingId
            ],
            [
                'no' => '007',
                'surat_id' => $suratMknId,
                'status' => 'Diserahkan ke Divisi',
                'harga' => 175000,
                'catatan' => 'Diserahkan ke divisi pengaju',
                'atasan_id' => $atasanId,
                'purchasing_id' => $purchasingId
            ],
            [
                'no' => '008',
                'surat_id' => $suratLogId,
                'status' => 'Menunggu Purchasing',
                'harga' => 700000,
                'catatan' => 'Urgent untuk perbaikan'
            ],
            [
                'no' => '009',
                'surat_id' => $suratMknId,
                'status' => 'Menunggu Approval Atasan',
                'harga' => 850000,
                'catatan' => 'Butuh approval karena harga tinggi',
                'purchasing_id' => $purchasingId
            ],
            [
                'no' => '010',
                'surat_id' => $suratAdmId,
                'status' => 'Ditolak Atasan',
                'harga' => 2000000,
                'catatan' => 'Ditolak karena anggaran tidak mencukupi',
                'atasan_id' => $atasanId,
                'purchasing_id' => $purchasingId
            ],
            [
                'no' => '011',
                'surat_id' => $suratAdmId,
                'status' => 'Disetujui Atasan',
                'harga' => 1200000,
                'catatan' => 'Disetujui untuk perawatan berkala',
                'atasan_id' => $atasanId,
                'purchasing_id' => $purchasingId
            ],
            [
                'no' => '012',
                'surat_id' => $suratMknId,
                'status' => 'Dibeli Purchasing',
                'harga' => 450000,
                'catatan' => 'Pembelian selesai, siap dikirim',
                'atasan_id' => $atasanId,
                'purchasing_id' => $purchasingId
            ],
            [
                'no' => '013',
                'surat_id' => $suratAdmId,
                'status' => 'Dikirim Purchasing',
                'harga' => 650000,
                'catatan' => 'Sudah dikirim ke logistik',
                'atasan_id' => $atasanId,
                'purchasing_id' => $purchasingId
            ],
            [
                'no' => '014',
                'surat_id' => $suratMknId,
                'status' => 'Diterima Logistik',
                'harga' => 285000,
                'catatan' => 'Barang diterima lengkap, siap diserahkan',
                'atasan_id' => $atasanId,
                'purchasing_id' => $purchasingId
            ],
        ];

        foreach ($additionalEntries as $entry) {
            PermintaanBarang::updateOrCreate(
                ['no_permintaan_barang' => $entry['no'] . '/LOG/KCE/' . now()->year],
                [
                    'id_surat_pengajuan' => $entry['surat_id'],
                    'tanggal_permintaan' => now()->toDateString(),
                    'id_status_wo' => $getStatusId($entry['status']),
                    'status' => $entry['status'],
                    'total_estimasi_harga' => $entry['harga'],
                    'catatan_atasan' => ($entry['atasan_id'] ?? null) ? ($entry['catatan'] ?? 'Disetujui') : null,
                    'catatan_purchasing' => ($entry['purchasing_id'] ?? null) ? ($entry['catatan'] ?? 'Selesai') : null,
                    'catatan_logistik' => $entry['catatan'],
                    'id_logistik' => $logistikId,
                    'id_purchasing' => $entry['purchasing_id'] ?? null,
                    'id_atasan' => $entry['atasan_id'] ?? null,
                    'id_akun' => $logistikId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}


