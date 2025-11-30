<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $table = 'unit';
    protected $primaryKey = 'id_unit';
    public $timestamps = true;

    protected $fillable = [
        'nama_unit',
        'kode_unit',
        'no_polisi',
        'jenis_unit',
        'merk_unit',
        'tahun_pembuatan',
    ];

    public function laporanHarianMekanik()
    {
        return $this->hasMany(LaporanHarianMekanik::class, 'id_unit');
    }

    public function laporanPemakaianBarang()
    {
        return $this->hasMany(LaporanPemakaianBarang::class, 'id_unit');
    }

    public function suratPengajuan()
    {
        return $this->hasMany(SuratPengajuan::class, 'id_unit');
    }
}
