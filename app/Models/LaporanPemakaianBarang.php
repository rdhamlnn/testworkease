<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanPemakaianBarang extends Model
{
    protected $table = 'laporan_pemakaian_barang';
    protected $primaryKey = 'id_laporan_pemakaian_barang';
    public $timestamps = true;

    protected $fillable = [
        'tanggal',
        'nama_barang',
        'kode_unit',
        'jumlah',
        'bentuk_satuan',
        'harga_satuan',
        'total_harga',
        'keterangan',
        'id_akun',
        'id_divisi',
        'id_unit',
    ];

    public function akun()
    {
        return $this->belongsTo(Akun::class, 'id_akun');
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'id_divisi');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'id_unit');
    }

    public function karyawan()
    {
        return $this->hasOneThrough(Karyawan::class, Akun::class, 'id_akun', 'id_karyawan', 'id_akun', 'id_karyawan');
    }
}
