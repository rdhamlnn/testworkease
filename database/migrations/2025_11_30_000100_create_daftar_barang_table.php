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
        Schema::create('daftar_barang', function (Blueprint $table) {
            $table->id('id_daftar_barang');
            $table->unsignedBigInteger('id_permintaan_barang');
            $table->string('nama_barang', 100);
            $table->integer('jumlah')->default(1);
            $table->string('satuan', 30)->nullable();
            $table->decimal('estimasi_harga', 15, 2)->nullable();
            $table->timestamps();

            $table->foreign('id_permintaan_barang')
                ->references('id_permintaan_barang')
                ->on('permintaan_barang')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });

        // Migrasikan data lama dari kolom JSON permintaan_barang.daftar_barang (jika masih ada)
        if (Schema::hasColumn('permintaan_barang', 'daftar_barang')) {
            $permintaanList = DB::table('permintaan_barang')
                ->select('id_permintaan_barang', 'daftar_barang')
                ->whereNotNull('daftar_barang')
                ->get();

            foreach ($permintaanList as $row) {
                $items = json_decode($row->daftar_barang, true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($items)) {
                    foreach ($items as $item) {
                        DB::table('daftar_barang')->insert([
                            'id_permintaan_barang' => $row->id_permintaan_barang,
                            'nama_barang' => $item['nama_barang'] ?? ($item['nama'] ?? '-'),
                            'jumlah' => isset($item['jumlah']) ? (int) $item['jumlah'] : 0,
                            'satuan' => $item['satuan'] ?? null,
                            'estimasi_harga' => isset($item['estimasi_harga'])
                                ? (float) $item['estimasi_harga']
                                : null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            Schema::table('permintaan_barang', function (Blueprint $table) {
                $table->dropColumn('daftar_barang');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tambahkan kembali kolom JSON (tanpa rekonstruksi data detail)
        if (!Schema::hasColumn('permintaan_barang', 'daftar_barang')) {
            Schema::table('permintaan_barang', function (Blueprint $table) {
                $table->text('daftar_barang')->nullable();
            });
        }

        Schema::dropIfExists('daftar_barang');
    }
};


