<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisWorkOrder extends Model
{
    protected $table = 'jenis_work_order';
    protected $primaryKey = 'id_jenis_wo';
    public $timestamps = true;

    protected $fillable = [
        'nama_jenis_wo',
        'deskripsi',
    ];

    /**
     * Get all work orders with this jenis
     */
    public function suratPengajuan()
    {
        return $this->hasMany(SuratPengajuan::class, 'id_jenis_wo');
    }
}
