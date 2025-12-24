<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratPengajuanRefrensi extends Model
{
    protected $table = 'surat_pengajuan_refrensi';
    protected $primaryKey = 'id_surat_pengajuan_refrensi';
    public $timestamps = true;

    protected $fillable = [
        'id_surat_pengajuan',
        'id_surat_pengajuan_referensi',
    ];

    /**
     * Relasi ke surat pengajuan utama
     */
    public function suratPengajuan()
    {
        return $this->belongsTo(SuratPengajuan::class, 'id_surat_pengajuan', 'id_surat_pengajuan');
    }

    /**
     * Relasi ke surat pengajuan referensi
     */
    public function suratPengajuanReferensi()
    {
        return $this->belongsTo(SuratPengajuan::class, 'id_surat_pengajuan_referensi', 'id_surat_pengajuan');
    }
}
