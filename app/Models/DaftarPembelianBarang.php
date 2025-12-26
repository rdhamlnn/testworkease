<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DaftarPembelianBarang extends Model
{
    protected $table = 'daftar_pembelian_barang';
    protected $primaryKey = 'id_daftar_pembelian_barang';
    public $timestamps = true;

    protected $fillable = [
        'id_barang',
        'id_surat_pengajuan',
        'jumlah',
        'harga_satuan',
        'total_harga',
    ];

    public function barang()
    {
        return $this->belongsTo(DaftarBarang::class, 'id_barang', 'id_daftar_barang');
    }

    public function workOrder()
    {
        return $this->belongsTo(SuratPengajuan::class, 'id_surat_pengajuan', 'id_surat_pengajuan');
    }
}
