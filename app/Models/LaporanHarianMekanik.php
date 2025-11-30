<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanHarianMekanik extends Model
{
    protected $table = 'laporan_harian_mekanik';
    protected $primaryKey = 'id_laporan_harian_mekanik';
    public $timestamps = true;

    protected $fillable = [
        'tanggal',
        'nama_unit',
        'keluhan_kerusakan',
        'penyebab_kerusakan',
        'tanggal_mulai',
        'tanggal_selesai',
        'tindakan_perbaikan',
        'id_akun',
        'id_divisi',
        'id_unit',
    ];

    public function akun()
    {
        return $this->belongsTo(Akun::class, 'id_akun');
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'id_divisi');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'id_unit');
    }

    public function karyawan()
    {
        return $this->hasOneThrough(Karyawan::class, Akun::class, 'id_akun', 'id_karyawan', 'id_akun', 'id_karyawan');
    }

    // Accessor untuk mendapatkan nama karyawan dari akun
    public function getNamaKaryawanAttribute()
    {
        return $this->akun ? $this->akun->karyawan->nama_lengkap ?? 'N/A' : 'N/A';
    }

    // Accessor untuk mendapatkan email dari akun
    public function getEmailKaryawanAttribute()
    {
        return $this->akun ? $this->akun->email ?? 'N/A' : 'N/A';
    }
}
