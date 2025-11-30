<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratPengajuan extends Model
{
    protected $table = 'surat_pengajuan';
    protected $primaryKey = 'id_surat_pengajuan';
    public $timestamps = true;

    protected $fillable = [
        'no_surat_pengajuan',
        'ditujukan',
        'id_jenis_wo',
        'tanggal',
        'divisi_pengaju',
        'unit',
        'uraian',
        'dokumentasi',
        'status',
        'status_dibaca',
        'id_divisi',
        'id_peran',
        'id_verifikator',
        'id_akun',
        'id_unit',
    ];

    /**
     * Relasi ke divisi tujuan berdasarkan id_divisi
     */
    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'id_divisi');
    }

    /**
     * Relasi opsional ke divisi pengaju berdasarkan nama divisi
     * Gunakan jika ingin memanggil $surat->divisiPengaju
     */
    public function divisiPengaju()
    {
        return $this->belongsTo(Divisi::class, 'divisi_pengaju', 'nama_divisi');
    }

    /**
     * Relasi ke role/peran akun
     */
    public function peran()
    {
        return $this->belongsTo(Peran::class, 'id_peran');
    }

    /**
     * Relasi ke status verifikator
     */
    public function verifikator()
    {
        return $this->belongsTo(StatusVerifikator::class, 'id_verifikator');
    }

    /**
     * Relasi ke akun pembuat WO
     */
    public function akun()
    {
        return $this->belongsTo(Akun::class, 'id_akun');
    }

    /**
     * Relasi ke unit
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'id_unit');
    }

    /**
     * Relasi ke jenis work order
     */
    public function jenisWorkOrder()
    {
        return $this->belongsTo(JenisWorkOrder::class, 'id_jenis_wo');
    }
}
