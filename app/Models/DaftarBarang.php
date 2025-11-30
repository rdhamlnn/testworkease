<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DaftarBarang extends Model
{
    protected $table = 'daftar_barang'; // master stok
    protected $primaryKey = 'id_daftar_barang';
    public $timestamps = true;

    protected $fillable = [
        'nama_barang',
        'satuan',
        'stok',
        'harga_barang',
        'path_foto',
    ];

    public function detailPermintaan()
    {
        return $this->hasMany(DetailBarangPermintaan::class, 'id_daftar_barang_master', 'id_daftar_barang');
    }
}


