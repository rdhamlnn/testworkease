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
        // Jika tabel daftar_barang sudah ada (saat ini berisi detail permintaan),
        // rename menjadi detail_barang_permintaan
        if (Schema::hasTable('daftar_barang') && !Schema::hasTable('detail_barang_permintaan')) {
            Schema::rename('daftar_barang', 'detail_barang_permintaan');
        }

        // Buat tabel master stok barang dengan nama daftar_barang
        if (!Schema::hasTable('daftar_barang')) {
            Schema::create('daftar_barang', function (Blueprint $table) {
                $table->id('id_daftar_barang');
                $table->string('nama_barang');
                $table->string('satuan')->nullable();
                $table->integer('stok')->default(0);
                $table->decimal('harga_barang', 15, 2)->nullable();
                $table->string('path_foto')->nullable();
                $table->timestamps();
            });
        } else {
            // Jika tabel sudah ada, tambahkan kolom baru jika belum ada
            Schema::table('daftar_barang', function (Blueprint $table) {
                if (!Schema::hasColumn('daftar_barang', 'harga_barang')) {
                    $table->decimal('harga_barang', 15, 2)->nullable()->after('stok');
                }
                if (!Schema::hasColumn('daftar_barang', 'path_foto')) {
                    $table->string('path_foto')->nullable()->after('harga_barang');
                }
            });
        }

        // Tambah kolom FK ke master daftar_barang di tabel detail (many-to-many)
        if (Schema::hasTable('detail_barang_permintaan') && !Schema::hasColumn('detail_barang_permintaan', 'id_daftar_barang_master')) {
            Schema::table('detail_barang_permintaan', function (Blueprint $table) {
                $table->unsignedBigInteger('id_daftar_barang_master')
                    ->nullable()
                    ->after('id_permintaan_barang');

                $table->foreign('id_daftar_barang_master')
                    ->references('id_daftar_barang')
                    ->on('daftar_barang')
                    ->onDelete('set null')
                    ->onUpdate('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus FK dan kolom pivot ke master
        if (Schema::hasTable('detail_barang_permintaan') && Schema::hasColumn('detail_barang_permintaan', 'id_daftar_barang_master')) {
            Schema::table('detail_barang_permintaan', function (Blueprint $table) {
                $table->dropForeign(['id_daftar_barang_master']);
                $table->dropColumn('id_daftar_barang_master');
            });
        }

        // Hapus tabel master stok
        if (Schema::hasTable('daftar_barang')) {
            Schema::dropIfExists('daftar_barang');
        }

        // Rename kembali detail ke daftar_barang jika perlu (rollback penuh)
        if (Schema::hasTable('detail_barang_permintaan') && !Schema::hasTable('daftar_barang')) {
            Schema::rename('detail_barang_permintaan', 'daftar_barang');
        }
    }
};


