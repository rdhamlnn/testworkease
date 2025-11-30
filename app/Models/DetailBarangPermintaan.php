<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailBarangPermintaan extends Model
{
    protected $table = 'detail_barang_permintaan';
    // Primary key tetap menggunakan kolom existing dari migrasi awal
    protected $primaryKey = 'id_daftar_barang';
    public $timestamps = true;

    protected $fillable = [
        'id_permintaan_barang',
        'id_daftar_barang_master',
        'nama_barang',
        'jumlah',
        'satuan',
        'estimasi_harga',
    ];

    public function permintaanBarang()
    {
        return $this->belongsTo(PermintaanBarang::class, 'id_permintaan_barang');
    }

    public function masterBarang()
    {
        return $this->belongsTo(DaftarBarang::class, 'id_daftar_barang_master', 'id_daftar_barang');
    }
}


