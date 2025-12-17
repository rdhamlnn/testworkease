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
        Schema::table('surat_pengajuan', function (Blueprint $table) {
            if (!Schema::hasColumn('surat_pengajuan', 'id_surat_pengajuan_parent')) {
                $table->unsignedBigInteger('id_surat_pengajuan_parent')->nullable()->after('id_surat_pengajuan');
            }
            if (!Schema::hasColumn('surat_pengajuan', 'harga_barang')) {
                $table->json('harga_barang')->nullable()->after('uraian');
            }
            if (!Schema::hasColumn('surat_pengajuan', 'total_harga')) {
                $table->decimal('total_harga', 15, 2)->nullable()->after('harga_barang');
            }
            if (!Schema::hasColumn('surat_pengajuan', 'catatan_penolakan')) {
                $table->text('catatan_penolakan')->nullable()->after('status');
            }
        });
        
        // Add foreign key only if it doesn't exist
        $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'surat_pengajuan' AND COLUMN_NAME = 'id_surat_pengajuan_parent' AND REFERENCED_TABLE_NAME IS NOT NULL");
        if (empty($foreignKeys)) {
            Schema::table('surat_pengajuan', function (Blueprint $table) {
                $table->foreign('id_surat_pengajuan_parent')->references('id_surat_pengajuan')->on('surat_pengajuan')->onDelete('set null')->onUpdate('cascade');
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_pengajuan', function (Blueprint $table) {
            if (Schema::hasColumn('surat_pengajuan', 'id_surat_pengajuan_parent')) {
                $table->dropForeign(['id_surat_pengajuan_parent']);
                $table->dropColumn('id_surat_pengajuan_parent');
            }
            if (Schema::hasColumn('surat_pengajuan', 'harga_barang')) {
                $table->dropColumn('harga_barang');
            }
            if (Schema::hasColumn('surat_pengajuan', 'total_harga')) {
                $table->dropColumn('total_harga');
            }
            if (Schema::hasColumn('surat_pengajuan', 'catatan_penolakan')) {
                $table->dropColumn('catatan_penolakan');
            }
        });
    }
};
