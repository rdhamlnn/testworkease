<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Peran extends Model
{
    protected $table = 'peran';
    protected $primaryKey = 'id_peran';
    public $timestamps = false;

    protected $fillable = [
        'nama_peran',
        'level',
        'deskripsi',
    ];

    public function akun()
    {
        return $this->hasMany(Akun::class, 'id_peran');
    }
}
