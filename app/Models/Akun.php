<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;

class Akun extends Model
{
    protected $table = 'akun';
    protected $primaryKey = 'id_akun';
    public $timestamps = true;

    protected $fillable = [
        'email',
        'password',
        'id_karyawan',
        'id_divisi',
        'id_peran',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'id_karyawan');
    }

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'id_divisi');
    }

    public function peran()
    {
        return $this->belongsTo(Peran::class, 'id_peran');
    }

    public function laporanHarianMekanik()
    {
        return $this->hasMany(LaporanHarianMekanik::class, 'id_akun');
    }

    public function laporanPemakaianBarang()
    {
        return $this->hasMany(LaporanPemakaianBarang::class, 'id_akun');
    }

    /**
     * Upsert an account record.
     *
     * @param string $email
     * @param string $password Plain password (will be hashed)
     * @param int $karyawanId
     * @param int $divisiId
     * @param int $peranId
     * @return void
     */
    public static function upsertAkun(string $email, string $password, int $karyawanId, int $divisiId, int $peranId): void
    {
        // Ensure peranId is valid
        if (!$peranId) {
            return;
        }
        $exists = DB::table('akun')->where('email', $email)->exists();
        $data = [
            'id_karyawan' => $karyawanId,
            'id_divisi'   => $divisiId,
            'id_peran'    => $peranId,
            'password'    => Hash::make($password),
            'updated_at'  => now(),
        ];
        if ($exists) {
            DB::table('akun')->where('email', $email)->update($data);
        } else {
            // Insert new record
            DB::table('akun')->insert(array_merge(['email' => $email, 'created_at' => now()], $data));
        }
    }

    public function suratPengajuan()
    {
        return $this->hasMany(SuratPengajuan::class, 'id_akun');
    }
}
