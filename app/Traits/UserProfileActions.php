<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\Akun;
use App\Models\Karyawan;

trait UserProfileActions
{
    /**
     * Display profile page.
     */
    public function profile()
    {
        $userId = Session::get('user_id');
        
        $user = Akun::with(['karyawan', 'divisi'])
            ->where('id_akun', $userId)
            ->first();
        
        return view($this->getProfileView(), compact('user'));
    }

    /**
     * Update profile.
     */
    public function updateProfile(Request $request)
    {
        $rules = [
            'nama_lengkap' => 'required|string|max:255',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string|max:255',
            'jabatan' => 'required|string|max:255',
        ];

        if ($request->has('email')) {
            $rules['email'] = 'required|email|unique:akun,email,' . Session::get('user_id') . ',id_akun';
        }

        $request->validate($rules);
        
        try {
            $userId = Session::get('user_id');
            $karyawanId = Session::get('user_karyawan');
            
            Karyawan::where('id_karyawan', $karyawanId)
                ->update([
                    'nama_lengkap' => $request->nama_lengkap,
                    'no_hp' => $request->no_hp,
                    'alamat' => $request->alamat,
                    'jabatan' => $request->jabatan,
                    'updated_at' => now(),
                ]);

            if ($request->has('email')) {
                Akun::where('id_akun', $userId)->update([
                    'email' => $request->email,
                    'updated_at' => now(),
                ]);
            }
            
            // Sync session
            $sessionData = [
                'nama_lengkap' => $request->nama_lengkap,
                'no_hp' => $request->no_hp,
                'alamat' => $request->alamat,
                'jabatan' => $request->jabatan,
                'updated_at' => now()
            ];

            if ($request->has('email')) {
                $sessionData['email'] = $request->email;
            }

            session($sessionData);
            session()->save();
            
            return redirect()->back()->with('success', 'Profil berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui profile: ' . $e->getMessage());
        }
    }


    /**
     * Change password.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);
        
        try {
            $user = Akun::find(Session::get('user_id'));
            
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->with('error', 'Password lama tidak sesuai!');
            }
            
            $user->update([
                'password' => Hash::make($request->new_password),
                'updated_at' => now(),
            ]);
            
            return redirect()->back()->with('success', 'Password berhasil diubah!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengubah password: ' . $e->getMessage());
        }
    }

    /**
     * Get the view name for profile.
     * Should be overridden in controller if different.
     */
    protected function getProfileView()
    {
        return str_replace('Controller', '', class_basename($this)) . '.profile';
    }
}
