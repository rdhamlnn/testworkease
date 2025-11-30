<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    /**
     * Display the profile page.
     */
    public function index()
    {
        return view('admin.profile');
    }

    /**
     * Update the user's profile.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string|max:500',
            'jabatan' => 'required|string|max:255'
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.max' => 'Nama lengkap maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 255 karakter.',
            'no_hp.max' => 'No. HP maksimal 20 karakter.',
            'alamat.max' => 'Alamat maksimal 500 karakter.',
            'jabatan.required' => 'Jabatan wajib diisi.',
            'jabatan.max' => 'Jabatan maksimal 255 karakter.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $userId = session('user_id');
            $karyawanId = session('user_karyawan');
            
            // Update data karyawan
            DB::table('karyawan')
                ->where('id_karyawan', $karyawanId)
                ->update([
                    'nama_lengkap' => $request->nama_lengkap,
                    'no_hp' => $request->no_hp,
                    'alamat' => $request->alamat,
                    'jabatan' => $request->jabatan,
                    'updated_at' => now(),
                ]);
            
            // Update email di tabel akun
            DB::table('akun')
                ->where('id_akun', $userId)
                ->update([
                    'email' => $request->email,
                    'updated_at' => now(),
                ]);

            // Upload foto dinonaktifkan

            // Update session data
            session([
                'nama_lengkap' => $request->nama_lengkap,
                'email' => $request->email,
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
                'jabatan' => $request->jabatan,
                'updated_at' => now()
            ]);
            
            // Save session to ensure it persists
            session()->save();

            return redirect()->route('admin.profile', ['from' => 'crud'])
                ->with('success', 'Data berhasil diperbarui');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui profile: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Change the user's password.
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
            'new_password_confirmation' => 'required|string|min:8'
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok.',
            'new_password_confirmation.required' => 'Konfirmasi password wajib diisi.',
            'new_password_confirmation.min' => 'Konfirmasi password minimal 8 karakter.'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $userId = session('user_id');
            
            // Get current user data
            $akun = DB::table('akun')->where('id_akun', $userId)->first();
            
            if (!$akun) {
                return redirect()->back()->with('error', 'User tidak ditemukan!');
            }
            
            // Verify current password
            if (!Hash::check($request->current_password, $akun->password)) {
                return redirect()->back()
                    ->with('error', 'Password lama tidak sesuai!')
                    ->withInput();
            }

            // Update password
            DB::table('akun')
                ->where('id_akun', $userId)
                ->update([
                    'password' => Hash::make($request->new_password),
                    'updated_at' => now(),
                ]);
            
            // Update session dengan password baru
            session(['password_updated_at' => now()]);

            return redirect()->route('admin.profile', ['from' => 'crud'])
                ->with('success', 'Password berhasil diubah!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat mengubah password: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Upload photo profile.
     */
    public function uploadPhoto(Request $request)
    {
        return redirect()->back()->with('error', 'Fitur upload foto dinonaktifkan.');
    }

    /**
     * Delete photo profile.
     */
    public function deletePhoto()
    {
        return redirect()->back()->with('error', 'Fitur foto profil dinonaktifkan.');
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