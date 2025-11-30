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
        Schema::create('unit', function (Blueprint $table) {
            $table->id('id_unit');
            $table->string('nama_unit');    
            $table->string('kode_unit');                
            $table->string('no_polisi')->nullable();
            $table->string('jenis_unit');
            $table->string('merk_unit')->nullable();
            $table->string('tahun_pembuatan')->nullable();
            $table->timestamps();  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit');
    }
};