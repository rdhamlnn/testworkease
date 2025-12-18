<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Karyawan extends Model
{
    protected $table = 'karyawan';
    protected $primaryKey = 'id_karyawan';
    public $timestamps = true;

    protected $fillable = [
        'nama_lengkap',
        'no_hp',
        'alamat',
        'id_divisi',
        'jabatan',
    ];

    protected $casts = [
        'id_divisi' => 'integer',
    ];

    public function akun()
    {
        return $this->hasOne(Akun::class, 'id_karyawan');
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'id_divisi');
    }

    /**
     * Upsert a karyawan record.
     *
     * @param int $id
     * @param string $nama
     * @param string $alamat
     * @param string $noHp
     * @param string $jabatan
     * @param int $divisiId
     * @return void
     */
    public static function upsertKaryawan(int $id, string $nama, string $alamat, string $noHp, string $jabatan, int $divisiId): void
    {
        DB::table('karyawan')->updateOrInsert(
            ['id_karyawan' => $id],
            [
                'nama_lengkap' => $nama,
                'alamat' => $alamat,
                'no_hp' => $noHp,
                'jabatan' => $jabatan,
                'id_divisi' => $divisiId,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
