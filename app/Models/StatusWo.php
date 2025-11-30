<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusWo extends Model
{
    protected $table = 'status_wo';
    protected $primaryKey = 'id_status_wo';
    public $timestamps = true;

    protected $fillable = [
        'nama_status',
    ];

    public function permintaan()
    {
        return $this->hasMany(PermintaanBarang::class, 'id_status_wo');
    }
}


