<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

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

        // Seed contoh permintaan barang - 001 (header saja)
        DB::table('permintaan_barang')->updateOrInsert(
            ['no_permintaan_barang' => '001/LOG/KCE/' . now()->year],
            [
                'id_surat_pengajuan' => $suratAdmId,
                'tanggal_permintaan' => now()->subDays(3)->toDateString(),
                'id_status_wo' => $getStatusId('Menunggu Purchasing'),
                'status' => 'Menunggu Purchasing',
                'total_estimasi_harga' => 600000,
                'catatan_logistik' => 'Urgent untuk perbaikan kebocoran',
                'id_logistik' => $logistikId,
                'id_purchasing' => null,
                'id_atasan' => null,
                'id_akun' => $logistikId,
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ]
        );

        // Seed contoh permintaan barang - 002
        DB::table('permintaan_barang')->updateOrInsert(
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
                'id_atasan' => null,
                'id_akun' => $logistikId,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ]
        );

        // Seed contoh permintaan barang - 003
        DB::table('permintaan_barang')->updateOrInsert(
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

        // Tambahan data untuk melengkapi status alur
        // Disetujui Atasan -> Dibeli Purchasing -> Diterima Logistik -> Diserahkan ke Divisi
        DB::table('permintaan_barang')->updateOrInsert(
            ['no_permintaan_barang' => '004/LOG/KCE/' . now()->year],
            [
                'id_surat_pengajuan' => $suratAdmId,
                'tanggal_permintaan' => now()->toDateString(),
                'id_status_wo' => $getStatusId('Disetujui Atasan'),
                'status' => 'Disetujui Atasan',
                'total_estimasi_harga' => 360000,
                'catatan_atasan' => 'Disetujui untuk perawatan berkala',
                'id_logistik' => $logistikId,
                'id_purchasing' => $purchasingId,
                'id_atasan' => $atasanId,
                'id_akun' => $logistikId,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('permintaan_barang')->updateOrInsert(
            ['no_permintaan_barang' => '005/LOG/KCE/' . now()->year],
            [
                'id_surat_pengajuan' => $suratMknId,
                'tanggal_permintaan' => now()->toDateString(),
                'id_status_wo' => $getStatusId('Dibeli Purchasing'),
                'status' => 'Dibeli Purchasing',
                'total_estimasi_harga' => 550000,
                'catatan_purchasing' => 'Pembelian selesai',
                'id_logistik' => $logistikId,
                'id_purchasing' => $purchasingId,
                'id_atasan' => $atasanId,
                'id_akun' => $logistikId,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('permintaan_barang')->updateOrInsert(
            ['no_permintaan_barang' => '006/LOG/KCE/' . now()->year],
            [
                'id_surat_pengajuan' => $suratAdmId,
                'tanggal_permintaan' => now()->toDateString(),
                'id_status_wo' => $getStatusId('Diterima Logistik'),
                'status' => 'Diterima Logistik',
                'total_estimasi_harga' => 225000,
                'catatan_logistik' => 'Barang diterima lengkap',
                'id_logistik' => $logistikId,
                'id_purchasing' => $purchasingId,
                'id_atasan' => $atasanId,
                'id_akun' => $logistikId,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('permintaan_barang')->updateOrInsert(
            ['no_permintaan_barang' => '007/LOG/KCE/' . now()->year],
            [
                'id_surat_pengajuan' => $suratMknId,
                'tanggal_permintaan' => now()->toDateString(),
                'id_status_wo' => $getStatusId('Diserahkan ke Divisi'),
                'status' => 'Diserahkan ke Divisi',
                'total_estimasi_harga' => 175000,
                'catatan_logistik' => 'Diserahkan ke divisi pengaju',
                'id_logistik' => $logistikId,
                'id_purchasing' => $purchasingId,
                'id_atasan' => $atasanId,
                'id_akun' => $logistikId,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Tambahan data untuk memastikan semua menu memiliki data
        // Menunggu Purchasing (untuk menu Permintaan Barang Purchasing)
        DB::table('permintaan_barang')->updateOrInsert(
            ['no_permintaan_barang' => '008/LOG/KCE/' . now()->year],
            [
                'id_surat_pengajuan' => $suratLogId,
                'tanggal_permintaan' => now()->subDays(1)->toDateString(),
                'id_status_wo' => $getStatusId('Menunggu Purchasing'),
                'status' => 'Menunggu Purchasing',
                'total_estimasi_harga' => 700000,
                'catatan_logistik' => 'Urgent untuk perbaikan',
                'id_logistik' => $logistikId,
                'id_purchasing' => null,
                'id_atasan' => null,
                'id_akun' => $logistikId,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ]
        );

        // Menunggu Approval Atasan (untuk menu Approval Permintaan Atasan)
        DB::table('permintaan_barang')->updateOrInsert(
            ['no_permintaan_barang' => '009/LOG/KCE/' . now()->year],
            [
                'id_surat_pengajuan' => $suratMknId,
                'tanggal_permintaan' => now()->subDays(1)->toDateString(),
                'id_status_wo' => $getStatusId('Menunggu Approval Atasan'),
                'status' => 'Menunggu Approval Atasan',
                'total_estimasi_harga' => 850000,
                'catatan_logistik' => 'Butuh approval karena harga tinggi',
                'catatan_purchasing' => 'Menunggu approval atasan',
                'id_logistik' => $logistikId,
                'id_purchasing' => $purchasingId,
                'id_atasan' => null,
                'id_akun' => $logistikId,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ]
        );

        // Ditolak Atasan (untuk menu Riwayat Approval Atasan)
        DB::table('permintaan_barang')->updateOrInsert(
            ['no_permintaan_barang' => '010/LOG/KCE/' . now()->year],
            [
                'id_surat_pengajuan' => $suratAdmId,
                'tanggal_permintaan' => now()->subDays(4)->toDateString(),
                'id_status_wo' => $getStatusId('Ditolak Atasan'),
                'status' => 'Ditolak Atasan',
                'total_estimasi_harga' => 2000000,
                'catatan_logistik' => 'Permintaan komponen premium',
                'catatan_purchasing' => 'Dikirim untuk approval',
                'catatan_atasan' => 'Ditolak karena anggaran tidak mencukupi',
                'id_logistik' => $logistikId,
                'id_purchasing' => $purchasingId,
                'id_atasan' => $atasanId,
                'id_akun' => $logistikId,
                'created_at' => now()->subDays(4),
                'updated_at' => now()->subDays(4),
            ]
        );

        // Tambahan data untuk Disetujui Atasan (untuk menu Beli Barang Purchasing)
        DB::table('permintaan_barang')->updateOrInsert(
            ['no_permintaan_barang' => '011/LOG/KCE/' . now()->year],
            [
                'id_surat_pengajuan' => $suratAdmId,
                'tanggal_permintaan' => now()->subDays(2)->toDateString(),
                'id_status_wo' => $getStatusId('Disetujui Atasan'),
                'status' => 'Disetujui Atasan',
                'total_estimasi_harga' => 1200000,
                'catatan_atasan' => 'Disetujui untuk perawatan berkala',
                'id_logistik' => $logistikId,
                'id_purchasing' => $purchasingId,
                'id_atasan' => $atasanId,
                'id_akun' => $logistikId,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ]
        );

        // Tambahan data untuk Dibeli Purchasing (untuk menu Kirim Barang Purchasing)
        DB::table('permintaan_barang')->updateOrInsert(
            ['no_permintaan_barang' => '012/LOG/KCE/' . now()->year],
            [
                'id_surat_pengajuan' => $suratMknId,
                'tanggal_permintaan' => now()->subDays(1)->toDateString(),
                'id_status_wo' => $getStatusId('Dibeli Purchasing'),
                'status' => 'Dibeli Purchasing',
                'total_estimasi_harga' => 450000,
                'catatan_purchasing' => 'Pembelian selesai, siap dikirim',
                'id_logistik' => $logistikId,
                'id_purchasing' => $purchasingId,
                'id_atasan' => $atasanId,
                'id_akun' => $logistikId,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ]
        );

        // Tambahan data untuk Dikirim Purchasing (untuk menu Terima Barang Logistik)
        DB::table('permintaan_barang')->updateOrInsert(
            ['no_permintaan_barang' => '013/LOG/KCE/' . now()->year],
            [
                'id_surat_pengajuan' => $suratAdmId,
                'tanggal_permintaan' => now()->toDateString(),
                'id_status_wo' => $getStatusId('Dikirim Purchasing'),
                'status' => 'Dikirim Purchasing',
                'total_estimasi_harga' => 650000,
                'catatan_logistik' => 'Menunggu penerimaan',
                'catatan_purchasing' => 'Sudah dikirim ke logistik',
                'catatan_atasan' => 'Disetujui',
                'id_logistik' => $logistikId,
                'id_purchasing' => $purchasingId,
                'id_atasan' => $atasanId,
                'id_akun' => $logistikId,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Tambahan data untuk Diterima Logistik (untuk menu Serahkan Barang Logistik)
        DB::table('permintaan_barang')->updateOrInsert(
            ['no_permintaan_barang' => '014/LOG/KCE/' . now()->year],
            [
                'id_surat_pengajuan' => $suratMknId,
                'tanggal_permintaan' => now()->toDateString(),
                'id_status_wo' => $getStatusId('Diterima Logistik'),
                'status' => 'Diterima Logistik',
                'total_estimasi_harga' => 285000,
                'catatan_logistik' => 'Barang diterima lengkap, siap diserahkan',
                'id_logistik' => $logistikId,
                'id_purchasing' => $purchasingId,
                'id_atasan' => $atasanId,
                'id_akun' => $logistikId,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}


