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
            if (!Schema::hasColumn('surat_pengajuan', 'id_jenis_wo')) {
                $table->unsignedBigInteger('id_jenis_wo')->nullable()->after('ditujukan');
            }
            // Kolom status_dibaca dihapus karena tidak digunakan
        });
        
        // Add foreign key only if it doesn't exist
        $foreignKeys = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'surat_pengajuan' AND COLUMN_NAME = 'id_jenis_wo' AND REFERENCED_TABLE_NAME IS NOT NULL");
        if (empty($foreignKeys)) {
            Schema::table('surat_pengajuan', function (Blueprint $table) {
                $table->foreign('id_jenis_wo')->references('id_jenis_wo')->on('jenis_work_order')->onDelete('set null')->onUpdate('cascade');
            });
        }
        
        // Drop status_dibaca column if exists (cleanup unused column)
        if (Schema::hasColumn('surat_pengajuan', 'status_dibaca')) {
            Schema::table('surat_pengajuan', function (Blueprint $table) {
                $table->dropColumn('status_dibaca');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_pengajuan', function (Blueprint $table) {
            if (Schema::hasColumn('surat_pengajuan', 'id_jenis_wo')) {
                $table->dropForeign(['id_jenis_wo']);
                $table->dropColumn('id_jenis_wo');
            }
        });
    }
};
