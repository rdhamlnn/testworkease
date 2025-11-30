<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Akun extends Model
{
    protected $table = 'akun';
    protected $primaryKey = 'id_akun';
    public $timestamps = true;

    protected $fillable = [
        'email',
        'password',
        'id_karyawan',
        'id_divisi',
        'id_peran',
        'is_active'
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan');
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'id_divisi');
    }

    public function peran()
    {
        return $this->belongsTo(Peran::class, 'id_peran');
    }

    public function laporanHarianMekanik()
    {
        return $this->hasMany(LaporanHarianMekanik::class, 'id_akun');
    }

    public function laporanPemakaianBarang()
    {
        return $this->hasMany(LaporanPemakaianBarang::class, 'id_akun');
    }

    public function suratPengajuan()
    {
        return $this->hasMany(SuratPengajuan::class, 'id_akun');
    }
}
