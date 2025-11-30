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
        Schema::create('permintaan_barang', function (Blueprint $table) {
            $table->id('id_permintaan_barang');
            $table->string('no_permintaan_barang')->unique();
            $table->unsignedBigInteger('id_surat_pengajuan');
            $table->date('tanggal_permintaan');
            $table->string('status')->default('Menunggu Logistik');
            $table->text('daftar_barang'); 
            $table->decimal('total_estimasi_harga', 15, 2)->nullable();
            $table->text('catatan_logistik')->nullable();
            $table->text('catatan_purchasing')->nullable();
            $table->text('catatan_atasan')->nullable();
            $table->unsignedBigInteger('id_logistik')->nullable();
            $table->unsignedBigInteger('id_purchasing')->nullable();
            $table->unsignedBigInteger('id_atasan')->nullable();
            $table->unsignedBigInteger('id_akun');
            $table->timestamps();
            
            $table->foreign('id_surat_pengajuan')->references('id_surat_pengajuan')->on('surat_pengajuan')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_akun')->references('id_akun')->on('akun')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_logistik')->references('id_akun')->on('akun')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('id_purchasing')->references('id_akun')->on('akun')->onDelete('set null')->onUpdate('cascade');
            $table->foreign('id_atasan')->references('id_akun')->on('akun')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permintaan_barang');
    }
};
