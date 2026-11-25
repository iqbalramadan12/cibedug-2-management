<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KasController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PengaturanRayonController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\PiketController;
use Illuminate\Support\Facades\Route;

// Halaman utama langsung mengarah ke Sign-In sesuai laporan
Route::get('/', [AuthController::class, 'showLogin']);

// Rute Autentikasi Publik (Tamu)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Logout (Hanya user yang sudah login)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Rute Dashboard Role: User / Siswa
Route::middleware(['auth', 'role:user'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'userDashboard'])->name('dashboard');
    Route::get('/profil', [AnggotaController::class, 'profilSaya'])->name('profil');
});
Route::get('/profil-saya', [AnggotaController::class, 'profilSaya'])->middleware(['auth', 'role:user'])->name('user.profil_alias');

// Rute Dashboard Role: Guru / Pembimbing Siswa
Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'guruDashboard'])->name('dashboard');
});

// Rute Dashboard Role: Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');
});

// Rute Modul Anggota Siswa (Phase 2 & Phase 5 DataTables)
Route::middleware(['auth'])->group(function () {
    // Endpoint DataTables AJAX Server-Side: Admin dan Guru
    Route::get('/anggota/data', [AnggotaController::class, 'data'])
        ->middleware('role:admin,guru')
        ->name('anggota.data');

    // Lihat daftar anggota: Admin dan Guru
    Route::get('/anggota', [AnggotaController::class, 'index'])
        ->middleware('role:admin,guru')
        ->name('anggota.index');

    // Lihat profil detail anggota: Admin dan Guru
    Route::get('/anggota/{anggota}', [AnggotaController::class, 'show'])
        ->middleware('role:admin,guru')
        ->name('anggota.show');

    // Kelola data anggota (Tambah, Ubah, Hapus): Hanya Admin
    Route::middleware('role:admin')->group(function () {
        Route::get('/anggota/create', [AnggotaController::class, 'create'])->name('anggota.create');
        Route::post('/anggota', [AnggotaController::class, 'store'])->name('anggota.store');
        Route::get('/anggota/{anggota}/edit', [AnggotaController::class, 'edit'])->name('anggota.edit');
        Route::put('/anggota/{anggota}', [AnggotaController::class, 'update'])->name('anggota.update');
        Route::delete('/anggota/{anggota}', [AnggotaController::class, 'destroy'])->name('anggota.destroy');
    });
});

// Rute Modul Jurnal Absensi (Phase 2 & Phase 4 Export)
Route::middleware(['auth'])->group(function () {
    // Export Laporan Absensi: Admin & Guru (Harus sebelum rute parameter dinamis)
    Route::middleware('role:admin,guru')->group(function () {
        Route::get('/absensi/export/excel', [AbsensiController::class, 'exportExcel'])->name('absensi.export.excel');
        Route::get('/absensi/export/pdf', [AbsensiController::class, 'exportPdf'])->name('absensi.export.pdf');
    });

    // Lihat absensi: Siswa (riwayat sendiri), Guru & Admin (seluruh siswa)
    Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');

    // Catat, ubah, dan hapus presensi: Admin dan Guru
    Route::middleware('role:admin,guru')->group(function () {
        Route::post('/absensi', [AbsensiController::class, 'store'])->name('absensi.store');
        Route::get('/absensi/{absensi}/edit', [AbsensiController::class, 'edit'])->name('absensi.edit');
        Route::put('/absensi/{absensi}', [AbsensiController::class, 'update'])->name('absensi.update');
        Route::delete('/absensi/{absensi}', [AbsensiController::class, 'destroy'])->name('absensi.destroy');
    });
});

// Rute Modul Jadwal Piket (Phase 3 & Phase 4 Export)
Route::middleware(['auth'])->group(function () {
    // Export Laporan Piket: Admin & Guru (Harus sebelum rute parameter dinamis)
    Route::middleware('role:admin,guru')->group(function () {
        Route::get('/piket/export/excel', [PiketController::class, 'exportExcel'])->name('piket.export.excel');
        Route::get('/piket/export/pdf', [PiketController::class, 'exportPdf'])->name('piket.export.pdf');
    });

    // Siswa (jadwal sendiri), Guru & Admin (seluruh siswa)
    Route::get('/piket', [PiketController::class, 'index'])->name('piket.index');

    // Pengelolaan Jadwal Piket: Admin dan Guru
    Route::middleware('role:admin,guru')->group(function () {
        Route::get('/piket/create', [PiketController::class, 'create'])->name('piket.create');
        Route::post('/piket', [PiketController::class, 'store'])->name('piket.store');
        Route::get('/piket/{piket}/edit', [PiketController::class, 'edit'])->name('piket.edit');
        Route::put('/piket/{piket}', [PiketController::class, 'update'])->name('piket.update');
        Route::patch('/piket/{piket}/status', [PiketController::class, 'updateStatus'])->name('piket.updateStatus');
        Route::delete('/piket/{piket}', [PiketController::class, 'destroy'])->name('piket.destroy');
    });
});

// Rute Modul Manajemen Keuangan Kas (Phase 4)
Route::middleware(['auth'])->group(function () {
    // Export Laporan Kas: Admin & Guru (Harus sebelum rute parameter dinamis)
    Route::middleware('role:admin,guru')->group(function () {
        Route::get('/kas/export/excel', [KasController::class, 'exportExcel'])->name('kas.export.excel');
        Route::get('/kas/export/pdf', [KasController::class, 'exportPdf'])->name('kas.export.pdf');
    });

    // Lihat kas: Admin & Guru (penuh), Siswa (read-only)
    Route::get('/kas', [KasController::class, 'index'])->name('kas.index');

    // CRUD Transaksi Kas: Admin & Guru
    Route::middleware('role:admin,guru')->group(function () {
        Route::get('/kas/create', [KasController::class, 'create'])->name('kas.create');
        Route::post('/kas', [KasController::class, 'store'])->name('kas.store');
        Route::get('/kas/{ka}/edit', [KasController::class, 'edit'])->name('kas.edit');
        Route::put('/kas/{ka}', [KasController::class, 'update'])->name('kas.update');
        Route::delete('/kas/{ka}', [KasController::class, 'destroy'])->name('kas.destroy');
    });
});

// Rute Modul Pusat Informasi & Pengumuman (Phase 5)
Route::middleware(['auth'])->group(function () {
    // Kelola Pengumuman: Admin & Guru (Create sebelum Show agar tidak tertangkap parameter dinamis)
    Route::middleware('role:admin,guru')->group(function () {
        Route::get('/pengumuman/create', [PengumumanController::class, 'create'])->name('pengumuman.create');
        Route::post('/pengumuman', [PengumumanController::class, 'store'])->name('pengumuman.store');
        Route::get('/pengumuman/{pengumuman}/edit', [PengumumanController::class, 'edit'])->name('pengumuman.edit');
        Route::put('/pengumuman/{pengumuman}', [PengumumanController::class, 'update'])->name('pengumuman.update');
        Route::delete('/pengumuman/{pengumuman}', [PengumumanController::class, 'destroy'])->name('pengumuman.destroy');
    });

    // Lihat daftar & detail pengumuman (Semua Role: Admin, Guru, Siswa sesuai filter)
    Route::get('/pengumuman', [PengumumanController::class, 'index'])->name('pengumuman.index');
    Route::get('/pengumuman/{pengumuman}', [PengumumanController::class, 'show'])->name('pengumuman.show');
});

// Rute Modul Laporan & Rekapitulasi Terpadu (Phase 5)
Route::middleware(['auth', 'role:admin,guru'])->group(function () {
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
});

// Rute Modul Pengaturan Rayon (Phase 7 - Khusus Admin)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/pengaturan-rayon', [PengaturanRayonController::class, 'index'])->name('pengaturan.index');
    Route::post('/pengaturan-rayon', [PengaturanRayonController::class, 'update'])->name('pengaturan.update');
    Route::delete('/pengaturan-rayon/foto', [PengaturanRayonController::class, 'destroyFoto'])->name('pengaturan.destroyFoto');
});
