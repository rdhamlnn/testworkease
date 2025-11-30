<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\Akun;
use App\Models\Karyawan;
use App\Models\Divisi;
use App\Models\Peran;


class KelolaAkunController extends Controller
{
    // Menampilkan daftar akun
    public function index()
    {
        $akun = DB::table('akun')
            ->join('karyawan', 'akun.id_karyawan', '=', 'karyawan.id_karyawan')
            ->join('divisi', 'akun.id_divisi', '=', 'divisi.id_divisi')
            ->join('peran', 'akun.id_peran', '=', 'peran.id_peran')
            ->select(
                'akun.id_akun',
                'akun.email',
                'karyawan.nama_lengkap',
                'divisi.nama_divisi',
                'peran.nama_peran'
            )
            ->orderBy('akun.id_akun', 'asc')
            ->get();

        // Ambil data untuk dropdown modal
        $divisi = DB::table('divisi')->get();
        $peran = DB::table('peran')->get();
        $karyawan = DB::table('karyawan')->get();

        return view('admin.kelola_akun', compact('akun', 'divisi', 'peran', 'karyawan'));
    }

    // Simpan akun baru
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:akun,email',
            'id_karyawan' => 'required',
            'id_divisi' => 'required',
            'id_peran' => 'required',
            'password' => 'required|min:6',
        ]);

        DB::table('akun')->insert([
            'email' => $request->email,
            'id_karyawan' => $request->id_karyawan,
            'id_divisi' => $request->id_divisi,
            'id_peran' => $request->id_peran,
            'password' => Hash::make($request->password),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Kembali ke halaman kelola akun dengan pesan sukses
        return redirect()->route('admin.kelola-akun', ['from' => 'crud'])->with('success', 'Data berhasil ditambahkan');
    }

    // Hapus akun
    public function destroy($id)
    {
        try {
            // Cek apakah akun ada
            $akun = DB::table('akun')->where('id_akun', $id)->first();

            if (!$akun) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan.'
                ], 404);
            }

            // Hapus akun
            DB::table('akun')->where('id_akun', $id)->delete();

            // Simpan success message di session sebelum return JSON
            session()->flash('success', 'Data berhasil dihapus!');
            session()->flash('from_crud', true);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus!',
                'redirect' => route('admin.kelola-akun')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'email' => 'required|email',
            'id_karyawan' => 'required',
            'id_divisi' => 'required',
            'id_peran' => 'required',
        ]);

        // Siapkan data update
        $data = [
            'email' => $request->email,
            'id_karyawan' => $request->id_karyawan,
            'id_divisi' => $request->id_divisi,
            'id_peran' => $request->id_peran,
            'updated_at' => now(),
        ];

        // Kalau password diisi, update juga
        if (!empty($request->password)) {
            $data['password'] = Hash::make($request->password);
        }

        // Jalankan update
        DB::table('akun')->where('id_akun', $id)->update($data);

        // Redirect ke halaman kelola akun + notifikasi sukses
        return redirect()->route('admin.kelola-akun', ['from' => 'crud'])->with('success', 'Data berhasil diperbarui');
    }

    // Ambil data akun untuk edit via AJAX
    public function getData($id)
    {
        $akun = DB::table('akun')
            ->where('id_akun', $id)
            ->first();

        if (!$akun) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($akun);
    }
}
