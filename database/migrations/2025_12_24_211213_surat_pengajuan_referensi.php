<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('surat_pengajuan_referensi', function (Blueprint $table) {
            $table->id('id_surat_pengajuan_referensi');
            $table->unsignedBigInteger('id_surat_pengajuan');
            $table->unsignedBigInteger('id_referensi');
            $table->timestamps();

            // Foreign key ke surat_pengajuan (surat utama)
            $table->foreign('id_surat_pengajuan')
                  ->references('id_surat_pengajuan')
                  ->on('surat_pengajuan')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            // Foreign key ke surat_pengajuan (surat referensi)
            $table->foreign('id_referensi')
                  ->references('id_surat_pengajuan')
                  ->on('surat_pengajuan')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_pengajuan_referensi');
    }
};
