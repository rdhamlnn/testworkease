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
        'id_surat_pengajuan_parent',
        'harga_barang',
        'total_harga',
        'catatan_penolakan',
    ];

    protected $casts = [
        'harga_barang' => 'array',
        'total_harga' => 'decimal:2',
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

    /**
     * Relasi ke work order parent (work order yang menjadi sumber/asal)
     */
    public function parent()
    {
        return $this->belongsTo(SuratPengajuan::class, 'id_surat_pengajuan_parent');
    }

    /**
     * Relasi ke work order children (work order yang dibuat dari work order ini)
     */
    public function children()
    {
        return $this->hasMany(SuratPengajuan::class, 'id_surat_pengajuan_parent');
    }

    /**
     * Relasi ke permintaan barang (jika ada)
     */
    public function permintaanBarang()
    {
        return $this->hasOne(PermintaanBarang::class, 'id_surat_pengajuan');
    }
}
