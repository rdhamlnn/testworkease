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
        Schema::dropIfExists('daftar_pembelian_barang');
        Schema::create('daftar_pembelian_barang', function (Blueprint $table) {
            $table->id('id_daftar_pembelian_barang');
            $table->unsignedBigInteger('id_barang'); // Referensi ke daftar_barang (master barang)
            $table->unsignedBigInteger('id_surat_pengajuan'); // Referensi ke work order
            $table->integer('jumlah');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('total_harga', 15, 2);
            $table->timestamps();

            // Foreign Keys
            $table->foreign('id_barang')
                  ->references('id_daftar_barang')
                  ->on('daftar_barang')
                  ->onDelete('cascade');

            $table->foreign('id_surat_pengajuan')
                  ->references('id_surat_pengajuan')
                  ->on('surat_pengajuan')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daftar_pembelian_barang');
    }
};
