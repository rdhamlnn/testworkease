<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Karyawan;
use App\Models\Divisi;

class KelolaKaryawanController extends Controller
{
    // Halaman utama daftar karyawan
    public function index()
    {
        $karyawan = Karyawan::with('divisi')->get();
        $divisi = Divisi::all();
        return view('admin.kelola_karyawan', compact('karyawan', 'divisi'));
    }

    // Simpan data karyawan baru
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nama_lengkap' => 'required|string|max:255',
                'no_hp' => 'required|string|max:20',
                'alamat' => 'required|string|max:255',
                'id_divisi' => 'required|exists:divisi,id_divisi',
                'jabatan' => 'required|string|max:255',
            ]);

            Karyawan::create([
                'nama_lengkap' => $validated['nama_lengkap'],
                'no_hp' => $validated['no_hp'],
                'alamat' => $validated['alamat'],
                'id_divisi' => $validated['id_divisi'],
                'jabatan' => $validated['jabatan'],
            ]);

            // Notifikasi sukses di halaman kelola karyawan
            return redirect()
                ->route('admin.kelola-karyawan', ['from' => 'crud'])
                ->with('success', 'Data berhasil ditambahkan');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Update data karyawan
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'nama_lengkap' => 'required|string|max:255',
                'no_hp' => 'required|string|max:20',
                'alamat' => 'required|string|max:255',
                'id_divisi' => 'required|exists:divisi,id_divisi',
                'jabatan' => 'required|string|max:255',
            ]);

            $karyawan = Karyawan::findOrFail($id);
            $karyawan->update($validated);

            return redirect()
                ->route('admin.kelola-karyawan', ['from' => 'crud'])
                ->with('success', 'Data berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Hapus karyawan
    public function destroy($id)
    {
        try {
            $karyawan = Karyawan::findOrFail($id);
            $karyawan->delete();

            // Simpan success message di session sebelum return JSON
            session()->flash('success', 'Data berhasil dihapus!');
            session()->flash('from_crud', true);

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus!',
                'redirect' => route('admin.kelola-karyawan')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get data karyawan untuk edit via AJAX
    public function getData($id)
    {
        $karyawan = Karyawan::with('divisi')->find($id);

        if (!$karyawan) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($karyawan);
    }
}
