<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermintaanBarang extends Model
{
    protected $table = 'permintaan_barang';
    protected $primaryKey = 'id_permintaan_barang';
    public $timestamps = true;

    protected $fillable = [
        'no_permintaan_barang',
        'id_surat_pengajuan',
        'tanggal_permintaan',
        'status',
        'id_status_wo',
        'total_estimasi_harga',
        'catatan_logistik',
        'catatan_purchasing',
        'catatan_atasan',
        'id_logistik',
        'id_purchasing',
        'id_atasan',
        'id_akun',
    ];

    public function suratPengajuan()
    {
        return $this->belongsTo(SuratPengajuan::class, 'id_surat_pengajuan');
    }

    public function akun()
    {
        return $this->belongsTo(Akun::class, 'id_akun');
    }

    public function logistik()
    {
        return $this->belongsTo(Akun::class, 'id_logistik');
    }

    public function purchasing()
    {
        return $this->belongsTo(Akun::class, 'id_purchasing');
    }

    public function atasan()
    {
        return $this->belongsTo(Akun::class, 'id_atasan');
    }

    public function statusWo()
    {
        return $this->belongsTo(StatusWo::class, 'id_status_wo');
    }

    /**
     * Detail barang permintaan (many-to-many ke master daftar_barang via pivot).
     */
    public function daftarBarang()
    {
        return $this->hasMany(DetailBarangPermintaan::class, 'id_permintaan_barang');
    }
}

