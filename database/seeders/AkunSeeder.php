<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Akun;
use App\Models\Karyawan;

class AkunSeeder extends Seeder
{
    public function run(): void
    {
        // Get specific divisions
        $adminDivisi = DB::table('divisi')->where('nama_divisi', 'Administrator')->first();
        $mekanikDivisi = DB::table('divisi')->where('nama_divisi', 'Mekanik')->first();
        $logistikDivisi = DB::table('divisi')->where('nama_divisi', 'Logistik')->first();
        $purchasingDivisi = DB::table('divisi')->where('nama_divisi', 'Purchasing')->first();
        $produksiDivisi = DB::table('divisi')->where('nama_divisi', 'Produksi')->first();
        $plasmaDivisi = DB::table('divisi')->where('nama_divisi', 'Plasma')->first();
        $qcDivisi = DB::table('divisi')->where('nama_divisi', 'Quality Control')->first();
        $atasanDivisi = DB::table('divisi')->where('nama_divisi', 'Atasan')->first();
        
        $adminDivisiId = $adminDivisi->id_divisi ?? 7;
        $mekanikDivisiId = $mekanikDivisi->id_divisi ?? 2;
        $logistikDivisiId = $logistikDivisi->id_divisi ?? 1;
        $purchasingDivisiId = $purchasingDivisi->id_divisi ?? 5;
        $produksiDivisiId = $produksiDivisi->id_divisi ?? 3;
        $plasmaDivisiId = $plasmaDivisi->id_divisi ?? 4;
        $qcDivisiId = $qcDivisi->id_divisi ?? 6;
        $atasanDivisiId = $atasanDivisi->id_divisi ?? 8;

        // Get specific role IDs (ensuring they exist)
        $adminRoleId = $this->getRoleId('Admin');
        $kadivRoleId = $this->getRoleId('Kadiv'); // adjust name if needed
        $karyawanRoleId = $this->getRoleId('Karyawan');
        $atasanRoleId = $this->getRoleId('Atasan');

        // Pastikan semua karyawan yang diperlukan ada
        $karyawanIds = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        
        foreach ($karyawanIds as $id) {
            $divisiId = match($id) {
                1 => $adminDivisiId,
                2, 3 => $mekanikDivisiId,
                4 => $logistikDivisiId,
                5 => $purchasingDivisiId,
                6 => $produksiDivisiId,
                7 => $plasmaDivisiId,
                8 => $qcDivisiId,
                9 => $atasanDivisiId,
                default => 1
            };
            
            $namaLengkap = match($id) {
                1 => 'Rudi Santoso',
                2 => 'Budi Hermawan',
                3 => 'Ahmad Wijaya',
                4 => 'Karyawan Logistik',
                5 => 'Karyawan Purchasing',
                6 => 'Karyawan Produksi',
                7 => 'Karyawan Plasma',
                8 => 'Karyawan QC',
                9 => 'Karyawan Atasan',
                default => 'Karyawan'
            };
            
            $jabatan = match($id) {
                1 => 'Administrator',
                2 => 'Kepala Divisi Mekanik',
                3 => 'Mekanik',
                4 => 'Staff Logistik',
                5 => 'Staff Purchasing',
                6 => 'Kepala Divisi Produksi',
                7 => 'Kepala Divisi Plasma',
                8 => 'Kepala Divisi Quality Control',
                9 => 'Atasan',
                default => 'Karyawan'
            };
            
            $noHp = '08123456789' . ($id - 1);
            
            Karyawan::upsertKaryawan($id, $namaLengkap, 'Jl. ' . $jabatan . ' No.' . $id, $noHp, $jabatan, $divisiId);
        }

        // Definisi data akun
        $akunData = [
            ['email' => 'admin@kce.com', 'id_karyawan' => 1, 'id_divisi' => $adminDivisiId, 'peran' => $adminRoleId],
            ['email' => 'kadivmekanik@kce.com', 'id_karyawan' => 2, 'id_divisi' => $mekanikDivisiId, 'peran' => $kadivRoleId],
            ['email' => 'mekanik@kce.com', 'id_karyawan' => 3, 'id_divisi' => $mekanikDivisiId, 'peran' => $karyawanRoleId],
            ['email' => 'logistik@kce.com', 'id_karyawan' => 4, 'id_divisi' => $logistikDivisiId, 'peran' => $kadivRoleId],
            ['email' => 'purchasing@kce.com', 'id_karyawan' => 5, 'id_divisi' => $purchasingDivisiId, 'peran' => $kadivRoleId],
            ['email' => 'kadivproduksi@kce.com', 'id_karyawan' => 6, 'id_divisi' => $produksiDivisiId, 'peran' => $kadivRoleId],
            ['email' => 'kadivplasma@kce.com', 'id_karyawan' => 7, 'id_divisi' => $plasmaDivisiId, 'peran' => $kadivRoleId],
            ['email' => 'kadivqc@kce.com', 'id_karyawan' => 8, 'id_divisi' => $qcDivisiId, 'peran' => $kadivRoleId],
            ['email' => 'atasan@kce.com', 'id_karyawan' => 9, 'id_divisi' => $atasanDivisiId, 'peran' => $atasanRoleId],
        ];

        foreach ($akunData as $data) {
            Akun::upsertAkun(
                $data['email'],
                'password',
                $data['id_karyawan'],
                $data['id_divisi'],
                $data['peran'] ?? 1
            );
        
        }
    }

    /**
     * Retrieve the ID of a role by its name.
     * Throws RuntimeException if the role does not exist.
     */
    private function getRoleId(string $roleName): int
    {
        $roleId = DB::table('peran')->where('nama_peran', $roleName)->value('id_peran');
        if (is_null($roleId)) {
            throw new \RuntimeException("Role '{$roleName}' not found in peran table.");
        }
        return (int) $roleId;
    }

    }
