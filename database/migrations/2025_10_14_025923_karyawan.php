<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('karyawan', function (Blueprint $table) {
            $table->id('id_karyawan');
            $table->string('nama_lengkap', 100);
            $table->text('alamat');
            $table->string('no_hp', 20);
            $table->string('jabatan', 50)->nullable();
            $table->timestamps();

            $table->unsignedBigInteger('id_divisi');
            $table->foreign('id_divisi')->references('id_divisi')->on('divisi')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawan');
    }
};
