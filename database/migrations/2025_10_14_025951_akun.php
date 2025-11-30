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
        Schema::create('akun', function (Blueprint $table) {
            $table->id('id_akun');
            $table->string('email');
            $table->string('password');
            $table->unsignedBigInteger('id_karyawan');
            $table->unsignedBigInteger('id_divisi');
            $table->unsignedBigInteger('id_peran');
            $table->timestamps();

            $table->foreign('id_karyawan')->references('id_karyawan')->on('karyawan')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_divisi')->references('id_divisi')->on('divisi')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_peran')->references('id_peran')->on('peran')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('akun');
    }
};