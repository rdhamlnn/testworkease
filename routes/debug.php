<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

// Debug route untuk melihat dan update harga barang
Route::get('/debug/daftar-barang', function () {
    $items = DB::table('daftar_barang')
        ->select('id_daftar_barang', 'nama_barang', 'harga_barang', 'stok', 'satuan')
        ->orderBy('nama_barang')
        ->get();
    
    return response()->json([
        'total_items' => $items->count(),
        'items' => $items
    ]);
});

// Update harga barang berdasarkan nama
Route::get('/debug/set-harga-barang', function () {
    // Default harga untuk barang umum (dalam Rupiah)
    $hargaDefault = [
        'Filter Oli' => 150000,
        'Filter Oil' => 150000,
        'Bearing Set' => 250000,
        'Belt Alternator' => 350000,
        'Oli Mesin' => 120000,
        'Kampas Rem' => 200000,
        'Aki' => 800000,
        'V-Belt' => 75000,
        'Busi' => 35000,
        'Lampu' => 50000,
    ];
    
    $updated = [];
    
    foreach ($hargaDefault as $nama => $harga) {
        $affected = DB::table('daftar_barang')
            ->where('nama_barang', 'LIKE', '%' . $nama . '%')
            ->whereNull('harga_barang')
            ->orWhere(function($q) use ($nama) {
                $q->where('nama_barang', 'LIKE', '%' . $nama . '%')
                  ->where('harga_barang', 0);
            })
            ->update(['harga_barang' => $harga]);
        
        if ($affected > 0) {
            $updated[$nama] = [
                'harga' => $harga,
                'affected_rows' => $affected
            ];
        }
    }
    
    return response()->json([
        'message' => 'Harga barang berhasil diupdate',
        'updated' => $updated
    ]);
});

// Cek detail barang permintaan untuk surat pengajuan tertentu
Route::get('/debug/detail-barang/{id_surat_pengajuan}', function ($id) {
    $details = DB::table('permintaan_barang as pb')
        ->join('detail_barang_permintaan as dbp', 'pb.id_permintaan_barang', '=', 'dbp.id_permintaan_barang')
        ->leftJoin('daftar_barang as db', 'dbp.id_daftar_barang_master', '=', 'db.id_daftar_barang')
        ->where('pb.id_surat_pengajuan', $id)
        ->select(
            'dbp.nama_barang',
            'dbp.jumlah',
            'dbp.satuan',
            'dbp.estimasi_harga',
            'dbp.id_daftar_barang_master',
            'db.nama_barang as master_nama',
            'db.harga_barang as master_harga'
        )
        ->get();
    
    $total = 0;
    foreach ($details as $d) {
        if ($d->estimasi_harga && $d->estimasi_harga > 0) {
            $total += $d->estimasi_harga;
        } elseif ($d->master_harga && $d->master_harga > 0) {
            $total += $d->master_harga * $d->jumlah;
        }
    }
    
    return response()->json([
        'id_surat_pengajuan' => $id,
        'details' => $details,
        'calculated_total' => $total
    ]);
});
