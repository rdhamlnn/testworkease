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
        Schema::create('laporan_pemakaian_barang', function (Blueprint $table) {
            $table->id('id_laporan_pemakaian_barang');
            $table->date('tanggal');
            $table->string('nama_barang');
            $table->string('kode_unit');
            $table->integer('jumlah');
            $table->string('bentuk_satuan', 30);
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('total_harga', 15, 2);
            $table->string('keterangan')->nullable();
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
        Schema::dropIfExists('laporan_pemakaian_barang');
    }
};
