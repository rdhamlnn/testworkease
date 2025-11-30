<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $table = 'karyawan';
    protected $primaryKey = 'id_karyawan';
    public $timestamps = true;

    protected $fillable = [
        'nama_lengkap',
        'no_hp',
        'alamat',
        'id_divisi',
        'jabatan',
    ];

    public function akun()
    {
        return $this->hasOne(Akun::class, 'id_karyawan');
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'id_divisi');
    }
}
