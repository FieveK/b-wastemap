<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PetaController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\AdminLaporanController;
use App\Http\Controllers\AdminPenangananController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\TitikSampahController;

// Halaman publik (tanpa login)
Route::get('/', [PetaController::class, 'index'])->name('peta');
Route::get('/api/titik-sampah', [PetaController::class, 'data'])->name('titik-sampah.data');
Route::get('/titik/{titikSampah}', [TitikSampahController::class, 'show'])->name('titik.show');

// Dashboard bawaan Breeze — JANGAN dihapus, dipakai link logo di navbar
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Wajib login, berlaku untuk semua role
Route::middleware('auth')->group(function () {
    Route::get('/laporan/buat', [LaporanController::class, 'create'])->name('laporan.create');
    Route::post('/laporan', [LaporanController::class, 'store'])->name('laporan.store');
    Route::get('/laporan/saya', [LaporanController::class, 'milikSaya'])->name('laporan.milik-saya');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Khusus admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/laporan', [AdminLaporanController::class, 'index'])->name('admin.laporan.index');
    Route::post('/laporan/{laporan}/verifikasi', [AdminLaporanController::class, 'verifikasi'])->name('admin.laporan.verifikasi');
    Route::post('/laporan/{laporan}/tolak', [AdminLaporanController::class, 'tolak'])->name('admin.laporan.tolak');
    Route::post('/laporan/{laporan}/tugaskan', [AdminPenangananController::class, 'tugaskan'])->name('admin.penanganan.tugaskan');
});

// Khusus petugas
Route::middleware(['auth', 'role:petugas'])->prefix('petugas')->group(function () {
    Route::get('/tugas', [PetugasController::class, 'index'])->name('petugas.tugas');
    Route::post('/tugas/{penanganan}/update', [PetugasController::class, 'update'])->name('petugas.tugas.update');
});

require __DIR__.'/auth.php';