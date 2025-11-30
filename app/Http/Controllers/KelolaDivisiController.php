<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KelolaDivisiController extends Controller
{
    // ============================================================
    // 🔹 TAMPILKAN DAFTAR DIVISI
    // Route: GET /admin/kelola-divisi
    // ============================================================
    public function index()
    {
        try {
            $divisi = DB::table('divisi')
                ->orderBy('id_divisi', 'asc')
                ->get();

            return view('admin.kelola_divisi', compact('divisi'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat data divisi: ' . $e->getMessage());
        }
    }

    // ============================================================
    // 🔹 SIMPAN DIVISI BARU
    // Route: POST /admin/simpan-divisi
    // ============================================================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_divisi' => 'required|string|max:100|unique:divisi,nama_divisi',
        ]);

        try {
            DB::table('divisi')->insert([
                'nama_divisi' => $validated['nama_divisi'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // ✅ Kembali ke kelola divisi
            return redirect()
                ->route('admin.kelola-divisi', ['from' => 'crud'])
                ->with('success', 'Data berhasil ditambahkan');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan divisi: ' . $e->getMessage());
        }
    }

    // ============================================================
    // 🔹 UPDATE DIVISI
    // Route: PUT /admin/divisi/update/{id}
    // ============================================================
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_divisi' => 'required|string|max:100|unique:divisi,nama_divisi,' . $id . ',id_divisi',
        ]);

        try {
            $updated = DB::table('divisi')
                ->where('id_divisi', $id)
                ->update([
                    'nama_divisi' => $validated['nama_divisi'],
                    'updated_at' => now(),
                ]);

            if ($updated) {
                return redirect()
                    ->route('admin.kelola-divisi', ['from' => 'crud'])
                    ->with('success', 'Data berhasil diperbarui');
            }

            return redirect()
                ->route('admin.kelola-divisi')
                ->with('error', 'Tidak ada perubahan yang dilakukan.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui divisi: ' . $e->getMessage());
        }
    }

    // ============================================================
    // 🔹 HAPUS DIVISI
    // Route: GET /admin/divisi/hapus/{id}
    // ============================================================
    public function destroy($id)
    {
        try {
            $deleted = DB::table('divisi')
                ->where('id_divisi', $id)
                ->delete();

            if ($deleted) {
                // Simpan success message di session sebelum return JSON
                session()->flash('success', 'Data berhasil dihapus!');
                session()->flash('from_crud', true);

                return response()->json([
                    'success' => true,
                    'message' => 'Data berhasil dihapus!',
                    'redirect' => route('admin.kelola-divisi')
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // 🔹 GET DATA DIVISI UNTUK EDIT VIA AJAX
    // Route: GET /admin/divisi/get/{id}
    // ============================================================
    public function getData($id)
    {
        $divisi = DB::table('divisi')
            ->where('id_divisi', $id)
            ->first();

        if (!$divisi) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($divisi);
    }
}
