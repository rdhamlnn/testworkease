<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tambah kolom hanya jika belum ada (hindari duplicate column error saat migrate ulang)
        if (!Schema::hasColumn('permintaan_barang', 'id_status_wo')) {
            Schema::table('permintaan_barang', function (Blueprint $table) {
                $table->unsignedBigInteger('id_status_wo')->nullable()->after('status');
            });
        }

        // Seed master status_wo (jika belum ada)
        $statuses = [
            'Menunggu Logistik',
            'Menunggu Purchasing',
            'Menunggu Approval Atasan',
            'Disetujui Atasan',
            'Ditolak Atasan',
            'Dibeli Purchasing',
            'Dikirim Purchasing',
            'Diterima Logistik',
            'Diserahkan ke Divisi',
        ];

        foreach ($statuses as $nama) {
            DB::table('status_wo')->updateOrInsert(
                ['nama_status' => $nama],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // Mapping status string lama ke id_status_wo
        $map = DB::table('status_wo')
            ->pluck('id_status_wo', 'nama_status')
            ->toArray();

        DB::table('permintaan_barang')
            ->select('id_permintaan_barang', 'status')
            ->orderBy('id_permintaan_barang')
            ->chunk(100, function ($rows) use ($map) {
                foreach ($rows as $row) {
                    if (!isset($map[$row->status])) {
                        continue;
                    }
                    DB::table('permintaan_barang')
                        ->where('id_permintaan_barang', $row->id_permintaan_barang)
                        ->update(['id_status_wo' => $map[$row->status]]);
                }
            });

        // Jadikan kolom wajib dan tambah foreign key
        Schema::table('permintaan_barang', function (Blueprint $table) {
            $table->unsignedBigInteger('id_status_wo')->nullable(false)->change();
            $table->foreign('id_status_wo')
                ->references('id_status_wo')
                ->on('status_wo')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permintaan_barang', function (Blueprint $table) {
            $table->dropForeign(['id_status_wo']);
            $table->dropColumn('id_status_wo');
        });
    }
};


