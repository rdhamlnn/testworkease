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
            $table->unsignedBigInteger('id_surat_pengajuan'); // FK ke work order
            $table->date('tanggal_permintaan');
            $table->string('status')->default('Menunggu Logistik');
            // Status: Menunggu Logistik, Diverifikasi Logistik, Menunggu Purchasing, 
            // Menunggu Approval Atasan, Disetujui Atasan, Ditolak Atasan, 
            // Dibeli Purchasing, Dikirim Purchasing, Diterima Logistik, Diserahkan ke Divisi
            $table->text('daftar_barang'); // JSON atau text (nama_barang, jumlah, satuan, estimasi_harga)
            $table->decimal('total_estimasi_harga', 15, 2)->nullable();
            $table->text('catatan_logistik')->nullable();
            $table->text('catatan_purchasing')->nullable();
            $table->text('catatan_atasan')->nullable();
            $table->unsignedBigInteger('id_logistik')->nullable(); // User logistik yang membuat
            $table->unsignedBigInteger('id_purchasing')->nullable(); // User purchasing yang handle
            $table->unsignedBigInteger('id_atasan')->nullable(); // User atasan yang approve
            $table->unsignedBigInteger('id_akun'); // User yang membuat
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
