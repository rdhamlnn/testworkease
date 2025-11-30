<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusVerifikator extends Model
{
    protected $table = 'status_verifikator';
    protected $primaryKey = 'id_verifikator';
    public $timestamps = false;

    protected $fillable = [
        'nama_status',
    ];

    public function suratPengajuan()
    {
        return $this->hasMany(SuratPengajuan::class, 'id_verifikator');
    }
}
