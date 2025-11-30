<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KelolaUnitController extends Controller
{
    // ============================================================
    // 🔹 TAMPILKAN DAFTAR UNIT
    // Route: GET /admin/kelola-unit
    // ============================================================
    public function index()
    {
        try {
            $unit = DB::table('unit')
                ->orderBy('id_unit', 'asc')
                ->get();

            return view('admin.kelola_unit', compact('unit'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat data unit: ' . $e->getMessage());
        }
    }

    // ============================================================
    // 🔹 SIMPAN UNIT BARU
    // Route: POST /admin/unit/simpan
    // ============================================================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_unit' => 'required|string|max:100',
            'kode_unit' => 'required|string|max:50|unique:unit,kode_unit',
            'no_polisi' => 'nullable|string|max:50',
            'jenis_unit' => 'required|string|max:50',
            'merk_unit' => 'nullable|string|max:50',
            'tahun_pembuatan' => 'nullable|integer|min:1900|max:' . date('Y'),
        ]);

        try {
            DB::table('unit')->insert([
                'nama_unit' => $validated['nama_unit'],
                'kode_unit' => $validated['kode_unit'],
                'no_polisi' => $validated['no_polisi'] ?? null,
                'jenis_unit' => $validated['jenis_unit'],
                'merk_unit' => $validated['merk_unit'] ?? null,
                'tahun_pembuatan' => $validated['tahun_pembuatan'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()
                ->route('admin.kelola-unit', ['from' => 'crud'])
                ->with('success', 'Data berhasil ditambahkan');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal menyimpan unit: ' . $e->getMessage());
        }
    }

    // ============================================================
    // 🔹 UPDATE UNIT
    // Route: POST /admin/unit/update/{id}
    // ============================================================
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_unit' => 'required|string|max:100',
            'kode_unit' => 'required|string|max:50|unique:unit,kode_unit,' . $id . ',id_unit',
            'no_polisi' => 'nullable|string|max:50',
            'jenis_unit' => 'required|string|max:50',
            'merk_unit' => 'nullable|string|max:50',
            'tahun_pembuatan' => 'nullable|integer|min:1900|max:' . date('Y'),
        ]);

        try {
            $updated = DB::table('unit')
                ->where('id_unit', $id)
                ->update([
                    'nama_unit' => $validated['nama_unit'],
                    'kode_unit' => $validated['kode_unit'],
                    'no_polisi' => $validated['no_polisi'] ?? null,
                    'jenis_unit' => $validated['jenis_unit'],
                    'merk_unit' => $validated['merk_unit'] ?? null,
                    'tahun_pembuatan' => $validated['tahun_pembuatan'] ?? null,
                    'updated_at' => now(),
                ]);

            if ($updated) {
                return redirect()
                    ->route('admin.kelola-unit', ['from' => 'crud'])
                    ->with('success', 'Data berhasil diperbarui');
            }

            return redirect()
                ->route('admin.kelola-unit')
                ->with('error', 'Tidak ada perubahan yang dilakukan.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memperbarui unit: ' . $e->getMessage());
        }
    }

    // ============================================================
    // 🔹 HAPUS UNIT
    // Route: GET /admin/unit/hapus/{id}
    // ============================================================
    public function destroy($id)
    {
        try {
            $deleted = DB::table('unit')->where('id_unit', $id)->delete();

            if ($deleted) {
                // Simpan success message di session sebelum return JSON
                session()->flash('success', 'Data berhasil dihapus!');
                session()->flash('from_crud', true);

                return response()->json([
                    'success' => true,
                    'message' => 'Data berhasil dihapus!',
                    'redirect' => route('admin.kelola-unit')
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
    // 🔹 GET DATA UNIT UNTUK EDIT VIA AJAX
    // Route: GET /admin/unit/get/{id}
    // ============================================================
    public function getData($id)
    {
        $unit = DB::table('unit')
            ->where('id_unit', $id)
            ->first();

        if (!$unit) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($unit);
    }
}
