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
        Schema::create('laporan_harian_mekanik', function (Blueprint $table) {
            $table->id('id_laporan_harian_mekanik');
            $table->date('tanggal');
            $table->string('nama_unit', 50);
            $table->text('keluhan_kerusakan');
            $table->text('penyebab_kerusakan');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->text('tindakan_perbaikan');
            $table->unsignedBigInteger('id_akun');
            $table->unsignedBigInteger('id_divisi');
            $table->unsignedBigInteger('id_unit');
            $table->timestamps();

            $table->foreign('id_akun')->references('id_akun')->on('akun')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_divisi')->references('id_divisi')->on('divisi')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_unit')->references('id_unit')->on('unit')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_harian_mekanik');
    }
};