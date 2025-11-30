<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'id_karyawan',
        'id_divisi',
        'id_peran',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // (opsional) relasi jika kamu punya tabel lain
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
}
