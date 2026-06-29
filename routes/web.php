<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\TargetBansosController;
use App\Http\Controllers\RealisasiBansosController;
use Illuminate\Support\Facades\Route;

// ============================================================
// PUBLIC — bisa diakses siapa saja (guest + admin)
// ============================================================

Route::get('/', fn() => redirect()->route('dashboard'));

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

// Endpoint JSON untuk chart dan tabel — dipanggil oleh Livewire
Route::get('/dashboard/data/chart', [DashboardController::class, 'dataChart'])
    ->name('dashboard.data.chart');

Route::get('/dashboard/data/tabel', [DashboardController::class, 'dataTabel'])
    ->name('dashboard.data.tabel');

// Dropdown wilayah bertingkat — dipanggil Livewire saat filter berubah
Route::get('/wilayah/anak/{wilayahId}', [DashboardController::class, 'anakWilayah'])
    ->name('wilayah.anak');

// Export — publik, tapi hanya data sesuai filter aktif
Route::get('/export', [ExportController::class, 'unduh'])
    ->name('export.unduh');

// ============================================================
// AUTH — route login/logout dari Laravel Breeze
// ============================================================

require __DIR__ . '/auth.php';

// ============================================================
// ADMIN — wajib login + role admin
// ============================================================

Route::middleware(['auth', 'admin.only'])->prefix('admin')->name('admin.')->group(function () {

    // Import data xlsx
    Route::get('/import', [ImportController::class, 'index'])
        ->name('import.index');

    Route::post('/import/upload', [ImportController::class, 'upload'])
        ->name('import.upload');

    Route::get('/import/review/{namaFile}', [ImportController::class, 'review'])
        ->name('import.review');

    Route::post('/import/proses', [ImportController::class, 'proses'])
        ->name('import.proses');

    Route::delete('/import/batalkan/{namaFile}', [ImportController::class, 'batalkan'])
        ->name('import.batalkan');

    // CRUD Target Bansos
    Route::resource('target', TargetBansosController::class)->except(['show']);

    // CRUD Realisasi Bansos
    Route::resource('realisasi', RealisasiBansosController::class)->except(['show']);
});