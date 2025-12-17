<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KelolaAkunController;
use App\Http\Controllers\KelolaDivisiController;
use App\Http\Controllers\KelolaUnitController;
use App\Http\Controllers\KelolaKaryawanController;
use App\Http\Controllers\WorkOrderController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\KadivMekanikController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LogistikController;
use App\Http\Controllers\PurchasingController;
use App\Http\Controllers\AtasanController;
use App\Http\Controllers\KadivProduksiController;
use App\Http\Controllers\KadivPlasmaController;
use App\Http\Controllers\KadivQcController;

// -------------------------------------------------------
// Redirect ke login
// -------------------------------------------------------
Route::get('/', function () {
    return redirect()->route('login');
});

// Public preview routes (bypass authentication for testing)
Route::get('/preview-laporan-harian-mekanik', [KadivMekanikController::class, 'previewLaporanHarianMekanik']);
Route::get('/download-laporan-harian-mekanik-pdf', [KadivMekanikController::class, 'downloadPdfLaporanHarianMekanik']);
Route::get('/download-laporan-harian-mekanik-excel', [KadivMekanikController::class, 'downloadExcelLaporanHarianMekanik']);

// Preview & Download untuk Laporan Pemakaian Barang (public preview)
Route::get('/preview-laporan-pemakaian-barang', [KadivMekanikController::class, 'previewLaporanPemakaianBarang']);
Route::get('/download-laporan-pemakaian-barang-pdf', [KadivMekanikController::class, 'downloadPdfLaporanPemakaianBarang']);
Route::get('/download-laporan-pemakaian-barang-excel', [KadivMekanikController::class, 'downloadExcelLaporanPemakaianBarang']);




// -------------------------------------------------------
// Authentication Routes
// -------------------------------------------------------
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.authenticate');
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// -------------------------------------------------------
// Protected Routes (Require Authentication)
// -------------------------------------------------------

// ---------------------------------------------------
// Admin Routes - Hanya untuk Admin (id_peran = 1)
// ---------------------------------------------------
Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // 🔹 DASHBOARD ADMIN
    Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');

    // 🔹 KELOLA AKUN
    Route::get('/kelola-akun', [KelolaAkunController::class, 'index'])->name('kelola-akun');
    Route::post('/simpan-akun', [KelolaAkunController::class, 'store'])->name('simpan-akun');
    Route::get('/akun/get/{id}', [KelolaAkunController::class, 'getData'])->name('get-akun');
    Route::put('/akun/update/{id}', [KelolaAkunController::class, 'update'])->name('update-akun');
    Route::delete('/akun/hapus/{id}', [KelolaAkunController::class, 'destroy'])->name('hapus-akun');

    // 🔹 KELOLA DIVISI
    Route::get('/kelola-divisi', [KelolaDivisiController::class, 'index'])->name('kelola-divisi');
    Route::post('/simpan-divisi', [KelolaDivisiController::class, 'store'])->name('simpan-divisi');
    Route::get('/divisi/get/{id}', [KelolaDivisiController::class, 'getData'])->name('get-divisi');
    Route::put('/divisi/update/{id}', [KelolaDivisiController::class, 'update'])->name('update-divisi');
    Route::delete('/divisi/hapus/{id}', [KelolaDivisiController::class, 'destroy'])->name('hapus-divisi');

    // 🔹 KELOLA UNIT
    Route::get('/kelola-unit', [KelolaUnitController::class, 'index'])->name('kelola-unit');
    Route::post('/simpan-unit', [KelolaUnitController::class, 'store'])->name('simpan-unit');
    Route::get('/unit/get/{id}', [KelolaUnitController::class, 'getData'])->name('get-unit');
    Route::put('/unit/update/{id}', [KelolaUnitController::class, 'update'])->name('update-unit');
    Route::delete('/unit/hapus/{id}', [KelolaUnitController::class, 'destroy'])->name('hapus-unit');

    // 🔹 KELOLA KARYAWAN
    Route::get('/kelola-karyawan', [KelolaKaryawanController::class, 'index'])->name('kelola-karyawan');
    Route::post('/simpan-karyawan', [KelolaKaryawanController::class, 'store'])->name('simpan-karyawan');
    Route::get('/karyawan/get/{id}', [KelolaKaryawanController::class, 'getData'])->name('get-karyawan');
    Route::put('/karyawan/update/{id}', [KelolaKaryawanController::class, 'update'])->name('update-karyawan');
    Route::delete('/karyawan/hapus/{id}', [KelolaKaryawanController::class, 'destroy'])->name('hapus-karyawan');

    // 🔹 WORK ORDER
    Route::get('/work-order', [App\Http\Controllers\WorkOrderController::class, 'index'])->name('work-order');
    Route::post('/simpan-work-order', [App\Http\Controllers\WorkOrderController::class, 'store'])->name('simpan-work-order');
    Route::get('/work-order/get/{id}', [App\Http\Controllers\WorkOrderController::class, 'getData'])->name('get-work-order');
    Route::put('/work-order/update/{id}', [App\Http\Controllers\WorkOrderController::class, 'update'])->name('update-work-order');
    Route::delete('/work-order/delete/{id}', [App\Http\Controllers\WorkOrderController::class, 'destroy'])->name('hapus-work-order');
    Route::post('/work-order/approve/{id}', [App\Http\Controllers\WorkOrderController::class, 'approve'])->name('approve-work-order');
    Route::post('/work-order/reject/{id}', [App\Http\Controllers\WorkOrderController::class, 'reject'])->name('reject-work-order');
    Route::get('/work-order/cetak/{id}', [App\Http\Controllers\WorkOrderController::class, 'cetakpdf'])->name('work-order.cetak');

    // 🔹 DAFTAR PENGAJUAN WORK ORDER
    Route::get('/daftar-pengajuan-work-order', [App\Http\Controllers\WorkOrderController::class, 'daftarPengajuan'])->name('daftar-pengajuan-work-order');

    // 🔹 LAPORAN & ARSIP
    Route::get('/laporan-harian-mekanik', [App\Http\Controllers\LaporanController::class, 'harianMekanik'])->name('laporan-harian-mekanik');
    Route::get('/laporan-pemakaian-barang', [App\Http\Controllers\LaporanController::class, 'pemakaianBarang'])->name('laporan-pemakaian-barang');
    Route::get('/laporan-arsip-wo', [App\Http\Controllers\LaporanController::class, 'arsipWo'])->name('laporan-arsip-wo');

    // 🔹 PROFILE
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile');
    Route::put('/profile/update', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/change-password', [App\Http\Controllers\ProfileController::class, 'changePassword'])->name('profile.change-password');
    Route::post('/profile/upload-photo', [App\Http\Controllers\ProfileController::class, 'uploadPhoto'])->name('profile.upload-photo');
    Route::delete('/profile/delete-photo', [App\Http\Controllers\ProfileController::class, 'deletePhoto'])->name('profile.delete-photo');

    // Laporan Filter Routes
    Route::post('/laporan/harian-mekanik/filter', [App\Http\Controllers\LaporanController::class, 'filterHarianMekanik'])->name('laporan.harian-mekanik.filter');
    Route::post('/laporan/pemakaian-barang/filter', [App\Http\Controllers\LaporanController::class, 'filterPemakaianBarang'])->name('laporan.pemakaian-barang.filter');
    Route::post('/laporan/arsip-wo/filter', [App\Http\Controllers\LaporanController::class, 'filterArsipWo'])->name('laporan.arsip-wo.filter');

    // Laporan Print & Export Routes
    Route::get('/laporan/harian-mekanik/print', [App\Http\Controllers\LaporanController::class, 'printHarianMekanik'])->name('laporan.harian-mekanik.print');
    Route::get('/laporan/pemakaian-barang/print', [App\Http\Controllers\LaporanController::class, 'printPemakaianBarang'])->name('laporan.pemakaian-barang.print');
    Route::get('/laporan/arsip-wo/print', [App\Http\Controllers\LaporanController::class, 'printArsipWo'])->name('laporan.arsip-wo.print');
    Route::get('/laporan/arsip-wo/print/{id}', [App\Http\Controllers\LaporanController::class, 'printArsipWoById'])->name('laporan.arsip-wo.print-by-id');

    // Laporan Export Routes
    Route::get('/laporan/harian-mekanik/export', [App\Http\Controllers\LaporanController::class, 'exportHarianMekanik'])->name('laporan.harian-mekanik.export');
    Route::get('/laporan/pemakaian-barang/export', [App\Http\Controllers\LaporanController::class, 'exportPemakaianBarang'])->name('laporan.pemakaian-barang.export');
    Route::get('/laporan/arsip-wo/export', [App\Http\Controllers\LaporanController::class, 'exportArsipWo'])->name('laporan.arsip-wo.export');

    // Template Routes
    Route::get('/template/laporan-harian-mekanik', [App\Http\Controllers\LaporanController::class, 'templateHarianMekanik'])->name('template.laporan-harian-mekanik');

    // General Laporan Routes
    Route::post('/laporan/filter', [App\Http\Controllers\LaporanController::class, 'filter'])->name('laporan.filter');
    Route::get('/laporan/print/{type}', [App\Http\Controllers\LaporanController::class, 'print'])->name('laporan.print');

    // Detail routes
    Route::get('/laporan-harian-mekanik/{id}', [App\Http\Controllers\LaporanController::class, 'detailHarianMekanik'])->name('laporan-harian-mekanik.detail');
    Route::get('/laporan-pemakaian-barang/{id}', [App\Http\Controllers\LaporanController::class, 'detailPemakaianBarang'])->name('laporan-pemakaian-barang.detail');
    Route::get('/work-order/{id}', [App\Http\Controllers\LaporanController::class, 'detailWorkOrder'])->name('work-order.detail');
});

// ---------------------------------------------------
// Kadiv Mekanik Routes - Hanya untuk Kadiv Mekanik (id_peran = 2)
// ---------------------------------------------------
Route::middleware(['role:kadiv'])->prefix('kadivmekanik')->name('kadivmekanik.')->group(function () {
    // 🔹 DASHBOARD
    Route::get('/dashboard', [KadivMekanikController::class, 'dashboard'])->name('dashboard');

    // 🔹 WORK ORDER
    Route::get('/work-order', [KadivMekanikController::class, 'workOrder'])->name('work-order');
    Route::get('/daftar-pengajuan-work-order', [KadivMekanikController::class, 'daftarPengajuanWorkOrder'])->name('daftar-pengajuan-work-order');
    Route::get('/riwayat-work-order', [KadivMekanikController::class, 'riwayatWorkOrder'])->name('riwayat-work-order');

    // Work Order CRUD
    Route::post('/work-order', [KadivMekanikController::class, 'storeWorkOrder'])->name('work-order.store');
    Route::get('/work-order/{id}', [KadivMekanikController::class, 'showWorkOrder'])->name('work-order.show');
    Route::put('/work-order/{id}', [KadivMekanikController::class, 'updateWorkOrder'])->name('work-order.update');
    Route::delete('/work-order/{id}', [KadivMekanikController::class, 'hapusWorkOrder'])->name('work-order.destroy');
    Route::delete('/work-order/delete/{id}', [KadivMekanikController::class, 'hapusWorkOrder'])->name('hapus-work-order');

    // Work Order Actions
    Route::put('/work-order/{id}/approve', [KadivMekanikController::class, 'approveWorkOrder'])->name('work-order.approve');
    Route::post('/work-order/approve/{id}', [KadivMekanikController::class, 'approveWorkOrder'])->name('approve-work-order');
    Route::put('/work-order/{id}/reject', [KadivMekanikController::class, 'rejectWorkOrder'])->name('work-order.reject');
    Route::post('/work-order/reject/{id}', [KadivMekanikController::class, 'rejectWorkOrder'])->name('reject-work-order');
    Route::put('/work-order/{id}/status', [KadivMekanikController::class, 'updateStatusWorkOrder'])->name('work-order.update-status');

    // Work Order Search & Filter
    Route::get('/work-order/search', [KadivMekanikController::class, 'searchWorkOrder'])->name('work-order.search');
    Route::post('/work-order/filter', [KadivMekanikController::class, 'filterWorkOrder'])->name('work-order.filter');
    Route::get('/work-order/export', [KadivMekanikController::class, 'exportWorkOrder'])->name('work-order.export');

    // 🔹 LAPORAN & ARSIP
    Route::get('/laporan-harian-mekanik', [KadivMekanikController::class, 'laporanHarianMekanik'])->name('laporan-harian-mekanik');
    Route::get('/laporan-pemakaian-barang', [KadivMekanikController::class, 'laporanPemakaianBarang'])->name('laporan-pemakaian-barang');

    // Laporan Harian Mekanik CRUD
    Route::post('/laporan-harian-mekanik', [KadivMekanikController::class, 'storeLaporanHarianMekanik'])->name('laporan-harian-mekanik.store');
    Route::post('/laporan/harian-mekanik/store', [KadivMekanikController::class, 'storeLaporanHarianMekanik'])->name('laporan.harian-mekanik.store');
    Route::post('/laporan/harian-mekanik/filter', [KadivMekanikController::class, 'filterLaporanHarianMekanik'])->name('laporan.harian-mekanik.filter');
    
    // Preview dan Download Routes untuk Laporan Harian Mekanik (harus sebelum route dengan {id})
    Route::get('/laporan-harian-mekanik/preview', [KadivMekanikController::class, 'previewLaporanHarianMekanik'])->name('laporan-harian-mekanik.preview');
    Route::get('/laporan-harian-mekanik/download/excel', [KadivMekanikController::class, 'downloadExcelLaporanHarianMekanik'])->name('laporan-harian-mekanik.download.excel');
    Route::get('/laporan-harian-mekanik/download/pdf', [KadivMekanikController::class, 'downloadPdfLaporanHarianMekanik'])->name('laporan-harian-mekanik.download.pdf');
    Route::get('/laporan-harian-mekanik/print', [KadivMekanikController::class, 'printLaporanHarianMekanik'])->name('laporan-harian-mekanik.print');
    
    // Route dengan parameter {id} harus setelah route spesifik
    Route::get('/laporan-harian-mekanik/{id}', [KadivMekanikController::class, 'showLaporanHarianMekanik'])->name('laporan-harian-mekanik.show');
    Route::put('/laporan-harian-mekanik/{id}', [KadivMekanikController::class, 'updateLaporanHarianMekanik'])->name('laporan-harian-mekanik.update');
    Route::delete('/laporan-harian-mekanik/{id}', [KadivMekanikController::class, 'destroyLaporanHarianMekanik'])->name('laporan-harian-mekanik.destroy');

    // Laporan Pemakaian Barang CRUD
    Route::post('/laporan-pemakaian-barang', [KadivMekanikController::class, 'storeLaporanPemakaianBarang'])->name('laporan-pemakaian-barang.store');
    Route::post('/laporan/pemakaian-barang/store', [KadivMekanikController::class, 'storeLaporanPemakaianBarang'])->name('laporan.pemakaian-barang.store');
    Route::get('/laporan-pemakaian-barang/{id}', [KadivMekanikController::class, 'showLaporanPemakaianBarang'])->name('laporan-pemakaian-barang.show');
    Route::put('/laporan-pemakaian-barang/{id}', [KadivMekanikController::class, 'updateLaporanPemakaianBarang'])->name('laporan-pemakaian-barang.update');
    Route::delete('/laporan-pemakaian-barang/{id}', [KadivMekanikController::class, 'destroyLaporanPemakaianBarang'])->name('laporan-pemakaian-barang.destroy');
    Route::post('/laporan/pemakaian-barang/filter', [KadivMekanikController::class, 'filterLaporanPemakaianBarang'])->name('laporan.pemakaian-barang.filter');
    Route::get('/laporan-pemakaian-barang/export', [KadivMekanikController::class, 'exportLaporanPemakaianBarang'])->name('laporan-pemakaian-barang.export');
    Route::get('/laporan-pemakaian-barang/print/{id}', [KadivMekanikController::class, 'printLaporanPemakaianBarang'])->name('laporan-pemakaian-barang.print');

    // General Laporan Routes
    Route::get('/laporan/dashboard', [KadivMekanikController::class, 'laporanDashboard'])->name('laporan.dashboard');
    Route::get('/laporan/statistik', [KadivMekanikController::class, 'laporanStatistik'])->name('laporan.statistik');

    // 🔹 PROFILE
    Route::get('/profile', [KadivMekanikController::class, 'profile'])->name('profile');
    Route::put('/profile/update', [KadivMekanikController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/change-password', [KadivMekanikController::class, 'changePassword'])->name('profile.change-password');
    Route::post('/profile/upload-photo', [KadivMekanikController::class, 'uploadPhoto'])->name('profile.upload-photo');
    Route::delete('/profile/delete-photo', [KadivMekanikController::class, 'deletePhoto'])->name('profile.delete-photo');

    // 🔹 NOTIFICATIONS
    Route::get('/notifications', [KadivMekanikController::class, 'notifications'])->name('notifications');
    Route::get('/notifications/unread', [KadivMekanikController::class, 'unreadNotifications'])->name('notifications.unread');
    Route::put('/notifications/{id}/read', [KadivMekanikController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::put('/notifications/mark-all-read', [KadivMekanikController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    // 🔹 SETTINGS
    Route::get('/settings', [KadivMekanikController::class, 'settings'])->name('settings');
    Route::put('/settings/update', [KadivMekanikController::class, 'updateSettings'])->name('settings.update');
    Route::get('/settings/activity-log', [KadivMekanikController::class, 'activityLog'])->name('settings.activity-log');

    // 🔹 API ROUTES (for AJAX calls)
    Route::get('/api/work-order-status/{id}', [KadivMekanikController::class, 'getWorkOrderStatus'])->name('api.work-order-status');
    Route::get('/api/laporan-count', [KadivMekanikController::class, 'getLaporanCount'])->name('api.laporan-count');
    Route::get('/api/dashboard-stats', [KadivMekanikController::class, 'getDashboardStats'])->name('api.dashboard-stats');
    Route::get('/api/recent-activities', [KadivMekanikController::class, 'getRecentActivities'])->name('api.recent-activities');

});

// ---------------------------------------------------
// Mekanik Routes - Hanya untuk Mekanik (id_peran = 4)
// ---------------------------------------------------
Route::middleware(['role:mekanik'])->prefix('mekanik')->name('mekanik.')->group(function () {
    // 🔹 DASHBOARD
    Route::get('/dashboard', [App\Http\Controllers\MekanikController::class, 'dashboard'])->name('dashboard');

    // 🔹 LAPORAN & ARSIP
    Route::get('/laporan-harian-mekanik', [App\Http\Controllers\MekanikController::class, 'laporanHarianMekanik'])->name('laporan-harian-mekanik');
    Route::get('/laporan-pemakaian-barang', [App\Http\Controllers\MekanikController::class, 'laporanPemakaianBarang'])->name('laporan-pemakaian-barang');

    // Laporan Harian Mekanik CRUD (Hanya Tambah dan Lihat)
    Route::post('/laporan-harian-mekanik', [App\Http\Controllers\MekanikController::class, 'storeLaporanHarianMekanik'])->name('laporan-harian-mekanik.store');
    Route::post('/laporan/harian-mekanik/store', [App\Http\Controllers\MekanikController::class, 'storeLaporanHarianMekanik'])->name('laporan.harian-mekanik.store');
    Route::get('/laporan-harian-mekanik/{id}', [App\Http\Controllers\MekanikController::class, 'showLaporanHarianMekanik'])->name('laporan-harian-mekanik.show');
    Route::post('/laporan/harian-mekanik/filter', [App\Http\Controllers\MekanikController::class, 'filterLaporanHarianMekanik'])->name('laporan.harian-mekanik.filter');

    // Laporan Pemakaian Barang CRUD (Hanya Tambah dan Lihat)
    Route::post('/laporan-pemakaian-barang', [App\Http\Controllers\MekanikController::class, 'storeLaporanPemakaianBarang'])->name('laporan-pemakaian-barang.store');
    Route::post('/laporan/pemakaian-barang/store', [App\Http\Controllers\MekanikController::class, 'storeLaporanPemakaianBarang'])->name('laporan.pemakaian-barang.store');
    Route::get('/laporan-pemakaian-barang/{id}', [App\Http\Controllers\MekanikController::class, 'showLaporanPemakaianBarang'])->name('laporan-pemakaian-barang.show');
    Route::post('/laporan/pemakaian-barang/filter', [App\Http\Controllers\MekanikController::class, 'filterLaporanPemakaianBarang'])->name('laporan.pemakaian-barang.filter');

    // 🔹 PROFILE
    Route::get('/profile', [App\Http\Controllers\MekanikController::class, 'profile'])->name('profile');
    Route::put('/profile/update', [App\Http\Controllers\MekanikController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/change-password', [App\Http\Controllers\MekanikController::class, 'changePassword'])->name('profile.change-password');
    Route::post('/profile/upload-photo', [App\Http\Controllers\MekanikController::class, 'uploadPhoto'])->name('profile.upload-photo');
    Route::delete('/profile/delete-photo', [App\Http\Controllers\MekanikController::class, 'deletePhoto'])->name('profile.delete-photo');

    // 🔹 API ROUTES (for AJAX calls)
    Route::get('/api/laporan-count', [App\Http\Controllers\MekanikController::class, 'getLaporanCount'])->name('api.laporan-count');
    Route::get('/api/dashboard-stats', [App\Http\Controllers\MekanikController::class, 'getDashboardStats'])->name('api.dashboard-stats');
    Route::get('/api/recent-activities', [App\Http\Controllers\MekanikController::class, 'getRecentActivities'])->name('api.recent-activities');
});

// ---------------------------------------------------
// Logistik Routes - Hanya untuk Logistik (divisi: Logistik, id_peran: 2)
// ---------------------------------------------------
Route::middleware(['role:kadiv'])->prefix('logistik')->name('logistik.')->group(function () {
    // 🔹 DASHBOARD
    Route::get('/dashboard', [LogistikController::class, 'dashboard'])->name('dashboard');

    // 🔹 WORK ORDER
    Route::get('/work-order', [LogistikController::class, 'workOrder'])->name('work-order');
    Route::get('/daftar-work-order', [LogistikController::class, 'daftarWorkOrder'])->name('daftar-work-order');
    Route::get('/riwayat-work-order', [LogistikController::class, 'riwayatWorkOrder'])->name('riwayat-work-order');
    Route::post('/work-order', [LogistikController::class, 'storeWorkOrder'])->name('work-order.store');
    Route::get('/work-order/detail/{id}', [LogistikController::class, 'showWorkOrder'])->name('work-order.show');
    Route::put('/work-order/{id}', [LogistikController::class, 'updateWorkOrder'])->name('work-order.update');
    Route::delete('/work-order/{id}', [LogistikController::class, 'destroyWorkOrder'])->name('work-order.destroy');
    Route::get('/work-order/cetak/{id}', [LogistikController::class, 'cetakpdf'])->name('work-order.cetak');

    // Work Order Approval
    Route::put('/work-order/{id}/approve', [LogistikController::class, 'approveWorkOrder'])->name('work-order.approve');
    Route::post('/work-order/approve/{id}', [LogistikController::class, 'approveWorkOrder'])->name('approve-work-order');
    Route::put('/work-order/{id}/reject', [LogistikController::class, 'rejectWorkOrder'])->name('work-order.reject');
    Route::post('/work-order/reject/{id}', [LogistikController::class, 'rejectWorkOrder'])->name('reject-work-order');
    Route::post('/proses-serahkan-barang-langsung/{id}', [LogistikController::class, 'prosesSerahkanBarangLangsung'])->name('proses-serahkan-barang-langsung');
    
    // Work Order Forward
    Route::post('/work-order/{id}/forward-to-purchasing', [LogistikController::class, 'forwardWorkOrderToPurchasing'])->name('work-order.forward-to-purchasing');

    // 🔹 PERMINTAAN BARANG
    Route::get('/permintaan-barang', [LogistikController::class, 'permintaanBarang'])->name('permintaan-barang');

    // 🔹 BARANG
    Route::get('/terima-barang', [LogistikController::class, 'terimaBarang'])->name('terima-barang');
    Route::post('/terima-barang/{id}', [LogistikController::class, 'prosesTerimaBarang'])->name('terima-barang.proses');
    Route::get('/serahkan-barang', [LogistikController::class, 'serahkanBarang'])->name('serahkan-barang');
    Route::post('/serahkan-barang/{id}', [LogistikController::class, 'prosesSerahkanBarang'])->name('serahkan-barang.proses');
    // 🔹 DAFTAR BARANG (Master Stok)
    Route::get('/daftar-barang', [LogistikController::class, 'daftarBarang'])->name('daftar-barang');
    Route::post('/daftar-barang', [LogistikController::class, 'storeDaftarBarang'])->name('daftar-barang.store');
    Route::get('/daftar-barang/{id}', [LogistikController::class, 'getDaftarBarang'])->name('daftar-barang.get');
    Route::post('/daftar-barang/{id}', [LogistikController::class, 'updateDaftarBarang'])->name('daftar-barang.update');
    Route::delete('/daftar-barang/{id}', [LogistikController::class, 'destroyDaftarBarang'])->name('daftar-barang.destroy');

    // 🔹 PERMINTAAN BARANG
    Route::get('/permintaan-barang/create/{id}', [LogistikController::class, 'createPermintaanBarang'])->name('permintaan-barang.create');
    Route::post('/permintaan-barang/store', [LogistikController::class, 'storePermintaanBarang'])->name('permintaan-barang.store');
    Route::get('/permintaan-barang/{id}/edit', [LogistikController::class, 'editPermintaanBarang'])->name('permintaan-barang.edit');
    Route::put('/permintaan-barang/{id}', [LogistikController::class, 'updatePermintaanBarang'])->name('permintaan-barang.update');
    Route::delete('/permintaan-barang/{id}', [LogistikController::class, 'destroyPermintaanBarang'])->name('permintaan-barang.destroy');

    // API untuk Work Order
    Route::get('/api/work-order/{id}', [LogistikController::class, 'showWorkOrder'])->name('api.work-order');
    Route::get('/api/units/search', [LogistikController::class, 'searchUnits'])->name('api.units.search');
    Route::get('/api/daftar-barang-stock', [LogistikController::class, 'getAllDaftarBarangStock'])->name('api.daftar-barang-stock');

    // 🔹 PROFILE
    Route::get('/profile', [LogistikController::class, 'profile'])->name('profile');
    Route::put('/profile/update', [LogistikController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/change-password', [LogistikController::class, 'changePassword'])->name('profile.change-password');
});

// ---------------------------------------------------
// Purchasing Routes - Hanya untuk Purchasing (divisi: Purchasing, id_peran: 2)
// ---------------------------------------------------
Route::middleware(['role:kadiv'])->prefix('purchasing')->name('purchasing.')->group(function () {
    // 🔹 DASHBOARD
    Route::get('/dashboard', [PurchasingController::class, 'dashboard'])->name('dashboard');

    // 🔹 WORK ORDER
    Route::get('/work-order', [PurchasingController::class, 'workOrder'])->name('work-order');
    Route::get('/daftar-work-order', [PurchasingController::class, 'daftarWorkOrder'])->name('daftar-work-order');
    Route::get('/riwayat-work-order', [PurchasingController::class, 'riwayatWorkOrder'])->name('riwayat-work-order');
    Route::get('/api/work-order/{id}', [PurchasingController::class, 'showWorkOrder'])->name('api.work-order');
    Route::get('/api/permintaan-barang/{id}', [PurchasingController::class, 'showPermintaanBarang'])->name('api.permintaan-barang');
    Route::get('/api/units/search', [PurchasingController::class, 'searchUnits'])->name('api.units.search');
    Route::post('/work-order', [PurchasingController::class, 'storeWorkOrder'])->name('work-order.store');
    Route::put('/work-order/{id}', [PurchasingController::class, 'updateWorkOrder'])->name('work-order.update');
    Route::delete('/work-order/{id}', [PurchasingController::class, 'destroyWorkOrder'])->name('work-order.destroy');
    Route::get('/work-order/cetak/{id}', [PurchasingController::class, 'cetak'])->name('work-order.cetak');

    // Work Order Approval
    Route::put('/work-order/{id}/approve', [PurchasingController::class, 'approveWorkOrder'])->name('work-order.approve');
    Route::post('/work-order/approve/{id}', [PurchasingController::class, 'approveWorkOrder'])->name('approve-work-order');
    Route::put('/work-order/{id}/reject', [PurchasingController::class, 'rejectWorkOrder'])->name('work-order.reject');
    Route::post('/work-order/reject/{id}', [PurchasingController::class, 'rejectWorkOrder'])->name('reject-work-order');
    
    // Work Order Harga & Forward
    Route::put('/work-order/{id}/update-harga', [PurchasingController::class, 'updateHargaWorkOrder'])->name('work-order.update-harga');
    Route::post('/work-order/{id}/forward-to-atasan', [PurchasingController::class, 'forwardWorkOrderToAtasan'])->name('work-order.forward-to-atasan');

    // 🔹 PERMINTAAN BARANG
    Route::get('/permintaan-barang', [PurchasingController::class, 'permintaanBarang'])->name('permintaan-barang');
    Route::get('/permintaan-barang/{id}/edit-harga', [PurchasingController::class, 'editHargaPermintaan'])->name('permintaan-barang.edit-harga');
    Route::put('/permintaan-barang/{id}/update-harga', [PurchasingController::class, 'updateHargaPermintaan'])->name('permintaan-barang.update-harga');

    // 🔹 PEMBELIAN
    Route::get('/beli-barang', [PurchasingController::class, 'beliBarang'])->name('beli-barang');
    Route::post('/beli-barang/{id}', [PurchasingController::class, 'prosesBeliBarang'])->name('beli-barang.proses');
    Route::get('/kirim-barang', [PurchasingController::class, 'kirimBarang'])->name('kirim-barang');
    Route::post('/kirim-barang/{id}', [PurchasingController::class, 'prosesKirimBarang'])->name('kirim-barang.proses');

    // 🔹 PERMINTAAN BARANG
    Route::post('/permintaan-barang/{id}/kirim-approval', [PurchasingController::class, 'kirimKeAtasan'])->name('permintaan-barang.kirim-approval');

    // 🔹 PROFILE
    Route::get('/profile', [PurchasingController::class, 'profile'])->name('profile');
    Route::put('/profile/update', [PurchasingController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/change-password', [PurchasingController::class, 'changePassword'])->name('profile.change-password');
});

// ---------------------------------------------------
// Atasan Routes - Hanya untuk Atasan (divisi: Atasan, id_peran: 4)
// ---------------------------------------------------
Route::middleware(['role:atasan'])->prefix('atasan')->name('atasan.')->group(function () {
    // 🔹 DASHBOARD
    Route::get('/dashboard', [AtasanController::class, 'dashboard'])->name('dashboard');

    // 🔹 APPROVAL
    Route::get('/approval-permintaan', [AtasanController::class, 'approvalPermintaan'])->name('approval-permintaan');
    Route::post('/approval-permintaan/{id}/approve', [AtasanController::class, 'approvePermintaan'])->name('approval-permintaan.approve');
    Route::post('/approval-permintaan/{id}/reject', [AtasanController::class, 'rejectPermintaan'])->name('approval-permintaan.reject');
    Route::get('/riwayat-approval', [AtasanController::class, 'riwayatApproval'])->name('riwayat-approval');

    // 🔹 WORK ORDER
    Route::get('/work-order-masuk', [AtasanController::class, 'workOrderMasuk'])->name('work-order-masuk');
    Route::get('/riwayat-work-order', [AtasanController::class, 'riwayatWorkOrder'])->name('riwayat-work-order');
    Route::get('/work-order/cetak/{id}', [AtasanController::class, 'cetakpdf'])->name('work-order.cetak');
    Route::get('/api/work-order/{id}', [AtasanController::class, 'showWorkOrder'])->name('api.work-order');
    Route::put('/work-order/{id}/approve', [AtasanController::class, 'approveWorkOrder'])->name('work-order.approve');
    Route::post('/work-order/approve/{id}', [AtasanController::class, 'approveWorkOrder'])->name('approve-work-order');
    Route::put('/work-order/{id}/reject', [AtasanController::class, 'rejectWorkOrder'])->name('work-order.reject');
    Route::post('/work-order/reject/{id}', [AtasanController::class, 'rejectWorkOrder'])->name('reject-work-order');

    // 🔹 PROFILE
    Route::get('/profile', [AtasanController::class, 'profile'])->name('profile');
    Route::put('/profile/update', [AtasanController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/change-password', [AtasanController::class, 'changePassword'])->name('profile.change-password');
});

// ---------------------------------------------------
// Kadiv Produksi Routes - Hanya untuk Kadiv Produksi (divisi: Produksi, id_peran: 2)
// ---------------------------------------------------
Route::middleware(['role:kadiv'])->prefix('kadivproduksi')->name('kadivproduksi.')->group(function () {
    // 🔹 DASHBOARD
    Route::get('/dashboard', [KadivProduksiController::class, 'dashboard'])->name('dashboard');

    // 🔹 WORK ORDER
    Route::get('/work-order', [KadivProduksiController::class, 'workOrder'])->name('work-order');
    Route::get('/daftar-work-order', [KadivProduksiController::class, 'daftarWorkOrder'])->name('daftar-work-order');
    Route::get('/riwayat-work-order', [KadivProduksiController::class, 'riwayatWorkOrder'])->name('riwayat-work-order');

    // Work Order CRUD
    Route::post('/work-order', [KadivProduksiController::class, 'storeWorkOrder'])->name('work-order.store');
    Route::get('/work-order/{id}', [KadivProduksiController::class, 'showWorkOrder'])->name('work-order.show');
    Route::put('/work-order/{id}', [KadivProduksiController::class, 'updateWorkOrder'])->name('work-order.update');
    Route::delete('/work-order/{id}', [KadivProduksiController::class, 'hapusWorkOrder'])->name('work-order.destroy');
    Route::delete('/work-order/delete/{id}', [KadivProduksiController::class, 'hapusWorkOrder'])->name('hapus-work-order');

    // Work Order Approval
    Route::put('/work-order/{id}/approve', [KadivProduksiController::class, 'approveWorkOrder'])->name('work-order.approve');
    Route::post('/work-order/approve/{id}', [KadivProduksiController::class, 'approveWorkOrder'])->name('approve-work-order');
    Route::put('/work-order/{id}/reject', [KadivProduksiController::class, 'rejectWorkOrder'])->name('work-order.reject');
    Route::post('/work-order/reject/{id}', [KadivProduksiController::class, 'rejectWorkOrder'])->name('reject-work-order');

    // 🔹 PROFILE
    Route::get('/profile', [KadivProduksiController::class, 'profile'])->name('profile');
    Route::put('/profile/update', [KadivProduksiController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/change-password', [KadivProduksiController::class, 'changePassword'])->name('profile.change-password');
});

// ---------------------------------------------------
// Kadiv Plasma Routes - Hanya untuk Kadiv Plasma (divisi: Plasma, id_peran: 2)
// ---------------------------------------------------
Route::middleware(['role:kadiv'])->prefix('kadivplasma')->name('kadivplasma.')->group(function () {
    // 🔹 DASHBOARD
    Route::get('/dashboard', [KadivPlasmaController::class, 'dashboard'])->name('dashboard');

    // 🔹 WORK ORDER
    Route::get('/work-order', [KadivPlasmaController::class, 'workOrder'])->name('work-order');
    Route::get('/daftar-work-order', [KadivPlasmaController::class, 'daftarWorkOrder'])->name('daftar-work-order');
    Route::get('/riwayat-work-order', [KadivPlasmaController::class, 'riwayatWorkOrder'])->name('riwayat-work-order');

    // Work Order CRUD
    Route::post('/work-order', [KadivPlasmaController::class, 'storeWorkOrder'])->name('work-order.store');
    Route::get('/work-order/{id}', [KadivPlasmaController::class, 'showWorkOrder'])->name('work-order.show');
    Route::put('/work-order/{id}', [KadivPlasmaController::class, 'updateWorkOrder'])->name('work-order.update');
    Route::delete('/work-order/{id}', [KadivPlasmaController::class, 'hapusWorkOrder'])->name('work-order.destroy');
    Route::delete('/work-order/delete/{id}', [KadivPlasmaController::class, 'hapusWorkOrder'])->name('hapus-work-order');

    // Work Order Approval
    Route::put('/work-order/{id}/approve', [KadivPlasmaController::class, 'approveWorkOrder'])->name('work-order.approve');
    Route::post('/work-order/approve/{id}', [KadivPlasmaController::class, 'approveWorkOrder'])->name('approve-work-order');
    Route::put('/work-order/{id}/reject', [KadivPlasmaController::class, 'rejectWorkOrder'])->name('work-order.reject');
    Route::post('/work-order/reject/{id}', [KadivPlasmaController::class, 'rejectWorkOrder'])->name('reject-work-order');

    // 🔹 PROFILE
    Route::get('/profile', [KadivPlasmaController::class, 'profile'])->name('profile');
    Route::put('/profile/update', [KadivPlasmaController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/change-password', [KadivPlasmaController::class, 'changePassword'])->name('profile.change-password');
});

// ---------------------------------------------------
// Kadiv QC Routes - Hanya untuk Kadiv Quality Control (divisi: Quality Control, id_peran: 2)
// ---------------------------------------------------
Route::middleware(['role:kadiv'])->prefix('kadivqc')->name('kadivqc.')->group(function () {
    // 🔹 DASHBOARD
    Route::get('/dashboard', [KadivQcController::class, 'dashboard'])->name('dashboard');

    // 🔹 WORK ORDER
    Route::get('/work-order', [KadivQcController::class, 'workOrder'])->name('work-order');
    Route::get('/daftar-work-order', [KadivQcController::class, 'daftarWorkOrder'])->name('daftar-work-order');
    Route::get('/riwayat-work-order', [KadivQcController::class, 'riwayatWorkOrder'])->name('riwayat-work-order');

    // Work Order CRUD
    Route::post('/work-order', [KadivQcController::class, 'storeWorkOrder'])->name('work-order.store');
    Route::get('/work-order/{id}', [KadivQcController::class, 'showWorkOrder'])->name('work-order.show');
    Route::put('/work-order/{id}', [KadivQcController::class, 'updateWorkOrder'])->name('work-order.update');
    Route::delete('/work-order/{id}', [KadivQcController::class, 'hapusWorkOrder'])->name('work-order.destroy');
    Route::delete('/work-order/delete/{id}', [KadivQcController::class, 'hapusWorkOrder'])->name('hapus-work-order');

    // Work Order Approval
    Route::put('/work-order/{id}/approve', [KadivQcController::class, 'approveWorkOrder'])->name('work-order.approve');
    Route::post('/work-order/approve/{id}', [KadivQcController::class, 'approveWorkOrder'])->name('approve-work-order');
    Route::put('/work-order/{id}/reject', [KadivQcController::class, 'rejectWorkOrder'])->name('work-order.reject');
    Route::post('/work-order/reject/{id}', [KadivQcController::class, 'rejectWorkOrder'])->name('reject-work-order');

    // 🔹 PROFILE
    Route::get('/profile', [KadivQcController::class, 'profile'])->name('profile');
    Route::put('/profile/update', [KadivQcController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/change-password', [KadivQcController::class, 'changePassword'])->name('profile.change-password');
});

// ---------------------------------------------------
// User Routes - Hanya untuk Karyawan (id_peran = 3)
// ---------------------------------------------------
Route::middleware(['role:user'])->group(function () {
    Route::get('/user/dashboard', [DashboardController::class, 'userDashboard'])->name('user.dashboard');
});

// -------------------------------------------------------
// Create Default Admin (Development Only)
// -------------------------------------------------------
if (app()->environment('local')) {
    Route::get('/create-admin', [AuthController::class, 'createDefaultAdmin'])->name('create.admin');
    Route::get('/create-kadivmekanik', [AuthController::class, 'createDefaultKadivMekanik'])->name('create.kadivmekanik');
    Route::get('/create-mekanik', [AuthController::class, 'createDefaultMekanik'])->name('create.mekanik');

    // Routes untuk membuat default user baru
    Route::get('/create-default-logistik', [AuthController::class, 'createDefaultLogistik'])->name('create-default-logistik');
    Route::get('/create-default-purchasing', [AuthController::class, 'createDefaultPurchasing'])->name('create-default-purchasing');
    Route::get('/create-default-atasan', [AuthController::class, 'createDefaultAtasan'])->name('create-default-atasan');
    Route::get('/create-default-kadiv-produksi', [AuthController::class, 'createDefaultKadivProduksi'])->name('create-default-kadiv-produksi');
    Route::get('/create-default-kadiv-plasma', [AuthController::class, 'createDefaultKadivPlasma'])->name('create-default-kadiv-plasma');
    Route::get('/create-default-kadiv-qc', [AuthController::class, 'createDefaultKadivQc'])->name('create-default-kadiv-qc');

    // Debug routes
    Route::get('/debug-session', function () {
        return response()->json([
            'user_id' => session('user_id'),
            'user_email' => session('user_email'),
            'user_divisi' => session('user_divisi'),
            'user_peran' => session('user_peran'),
            'user_karyawan' => session('user_karyawan'),
            'all_session' => session()->all()
        ]);
    });

    Route::get('/test-kadivmekanik', function () {
        return 'Test route kadivmekanik works!';
    });

    // Test route without middleware
    Route::get('/test-kadivmekanik-dashboard', [KadivMekanikController::class, 'dashboard'])->name('test.kadivmekanik.dashboard');
    Route::get('/test-kadivmekanik-controller', [KadivMekanikController::class, 'test'])->name('test.kadivmekanik.controller');

    // Test route with manual session setup
    Route::get('/setup-kadivmekanik-session', function () {
        session(['user_id' => 1]);
        session(['user_email' => 'kadivmekanik@kce.com']);
        session(['user_divisi' => 1]);
        session(['user_peran' => 2]);
        session(['user_karyawan' => 1]);
        session()->save();

        return redirect()->route('kadivmekanik.dashboard')->with('success', 'Session setup for kadivmekanik!');
    });

}
