<?php

use App\Http\Controllers\AdminAnalyticsController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminLaporanController;
use App\Http\Controllers\AdminNotifikasiController;
use App\Http\Controllers\AdminPenangananController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LaporanPelacakanController;
use App\Http\Controllers\PetaController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TitikSampahController;
use Illuminate\Support\Facades\Route;

// Halaman publik (tanpa login)
Route::get('/', [PetaController::class, 'landing'])->name('home');
Route::get('/peta', [PetaController::class, 'index'])->name('peta');
Route::get('/api/titik-sampah', [PetaController::class, 'data'])->name('titik-sampah.data');
Route::get('/titik/{titikSampah}', [TitikSampahController::class, 'show'])->name('titik.show');
Route::get('/laporan/buat', [LaporanController::class, 'create'])->name('laporan.create');
Route::post('/laporan/lokasi-foto', [LaporanController::class, 'extractLokasiFoto'])
    ->middleware('throttle:20,1')
    ->name('laporan.lokasi-foto');
Route::post('/laporan', [LaporanController::class, 'store'])
    ->middleware('throttle:5,10')
    ->name('laporan.store');
Route::get('/laporan/berhasil', [LaporanController::class, 'berhasil'])->name('laporan.berhasil');
Route::get('/lacak', [LaporanPelacakanController::class, 'index'])->name('laporan.lacak');
Route::post('/lacak', [LaporanPelacakanController::class, 'cari'])
    ->middleware('throttle:20,1')
    ->name('laporan.lacak.cari');
Route::get('/lacak/{token}', [LaporanPelacakanController::class, 'tampilkan'])
    ->where('token', 'BWM-[A-Za-z0-9]{32}')
    ->middleware('throttle:30,1')
    ->name('laporan.lacak.token');

// Beranda akun mengikuti peran pengguna
Route::get('/dashboard', function () {
    return match (auth()->user()->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'petugas' => redirect()->route('petugas.tugas'),
        default => redirect()->route('peta'),
    };
})->middleware('auth')->name('dashboard');

// Wajib login, berlaku untuk semua role
Route::middleware('auth')->group(function () {
    Route::get('/laporan/saya', [LaporanController::class, 'milikSaya'])->name('laporan.milik-saya');
    Route::get('/laporan/{laporan}/foto', [LaporanController::class, 'foto'])->name('laporan.foto');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Khusus admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/pengguna', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::post('/pengguna', [AdminUserController::class, 'store'])->name('admin.users.store');
    Route::patch('/pengguna/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
    Route::get('/analitik', [AdminAnalyticsController::class, 'index'])->name('admin.analitik');
    Route::get('/analitik/ekspor', [AdminAnalyticsController::class, 'export'])->name('admin.analitik.export');
    Route::get('/laporan', [AdminLaporanController::class, 'index'])->name('admin.laporan.index');
    Route::get('/notifikasi/laporan', [AdminNotifikasiController::class, 'belumDibaca'])->name('admin.notifikasi.laporan');
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
