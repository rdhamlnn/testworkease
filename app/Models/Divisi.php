<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Divisi extends Model
{
    protected $table = 'divisi';
    protected $primaryKey = 'id_divisi';
    public $timestamps = true;

    protected $fillable = [
        'nama_divisi',
    ];

    public function akun()
    {
        return $this->hasMany(Akun::class, 'id_divisi');
    }
}
