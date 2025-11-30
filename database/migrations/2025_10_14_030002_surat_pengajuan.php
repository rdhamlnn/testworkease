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
        Schema::create('surat_pengajuan', function (Blueprint $table) {
            $table->id('id_surat_pengajuan');
            $table->string('no_surat_pengajuan');
            $table->string('ditujukan');
            $table->date('tanggal');
            $table->string('divisi_pengaju');
            $table->string('unit');
            $table->string('uraian');
            $table->string('dokumentasi')->nullable();
            $table->string('status')->default('Menunggu');
            $table->unsignedBigInteger('id_divisi');
            $table->unsignedBigInteger('id_peran');
            $table->unsignedBigInteger('id_verifikator');
            $table->unsignedBigInteger('id_akun');
            $table->unsignedBigInteger('id_unit');
            $table->timestamps();

            $table->foreign('id_divisi')->references('id_divisi')->on('divisi')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_peran')->references('id_peran')->on('peran')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_verifikator')->references('id_verifikator')->on('status_verifikator')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_akun')->references('id_akun')->on('akun')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_unit')->references('id_unit')->on('unit')->onDelete('cascade')->onUpdate('cascade');
        });
    }   

    /**
     * Reverse the migrations.  
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_pengajuan');
    }
};