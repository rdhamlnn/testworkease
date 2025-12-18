<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    use \App\Traits\UserProfileActions;

    /**
     * Get the view name for profile.
     */
    protected function getProfileView()
    {
        return 'admin.profile';
    }

    public function index()
    {
        return $this->profile();
    }




    /**
     * Get user profile data.
     */
    public function getProfileData()
    {
        $profileData = [
            'nama_lengkap' => session('nama_lengkap', 'Admin User'),
            'email' => session('email', 'admin@kce.com'),
            'no_hp' => session('no_hp', '081234567890'),
            'alamat' => session('alamat', 'Jl. Contoh Alamat No. 123'),
            'divisi' => session('divisi', 'Administrator'),
            'role' => session('role', 'Administrator'),
            // Foto dihapus dari sistem
            'last_login' => session('last_login', 'Hari ini'),
            'created_at' => session('created_at', 'Januari 2024'),
            'status' => 'Aktif'
        ];

        return response()->json($profileData);
    }
}