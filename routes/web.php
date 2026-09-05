<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DasWebController;
use App\Http\Controllers\Web\JuruSungaiWebController;
use App\Http\Controllers\Web\LaporanWebController;
use App\Http\Controllers\Web\LoginController;
use App\Http\Controllers\Web\PetaWebController;
use App\Http\Controllers\Web\RekapWebController;
use App\Http\Controllers\Web\StafDinasWebController;
use App\Http\Controllers\Web\WilayahSungaiWebController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest:staf')->group(function () {
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.proses');
});

Route::middleware('auth:staf')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Halaman yang boleh dilihat semua staf, termasuk viewer.
    Route::get('/laporan', [LaporanWebController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/export', [LaporanWebController::class, 'export'])->name('laporan.export');
    Route::get('/laporan/{laporan}', [LaporanWebController::class, 'show'])->name('laporan.show');

    Route::get('/peta', [PetaWebController::class, 'index'])->name('peta.index');

    Route::get('/rekap', [RekapWebController::class, 'index'])->name('rekap.index');
    Route::get('/rekap/export', [RekapWebController::class, 'export'])->name('rekap.export');

    Route::get('/juru', [JuruSungaiWebController::class, 'index'])->name('juru.index');
    Route::get('/das', [DasWebController::class, 'index'])->name('das.index');
    Route::get('/wilayah', [WilayahSungaiWebController::class, 'index'])->name('wilayah.index');

    // Perubahan data hanya untuk admin. Didaftarkan sebelum rute {id}
    // supaya "create" tidak tertangkap sebagai parameter.
    Route::middleware('admin')->group(function () {
        Route::get('/juru/create', [JuruSungaiWebController::class, 'create'])->name('juru.create');
        Route::post('/juru', [JuruSungaiWebController::class, 'store'])->name('juru.store');
        Route::get('/juru/{juru}/edit', [JuruSungaiWebController::class, 'edit'])->name('juru.edit');
        Route::put('/juru/{juru}', [JuruSungaiWebController::class, 'update'])->name('juru.update');
        Route::delete('/juru/{juru}', [JuruSungaiWebController::class, 'destroy'])->name('juru.destroy');

        Route::get('/das/create', [DasWebController::class, 'create'])->name('das.create');
        Route::post('/das', [DasWebController::class, 'store'])->name('das.store');
        Route::get('/das/{das}/edit', [DasWebController::class, 'edit'])->name('das.edit');
        Route::put('/das/{das}', [DasWebController::class, 'update'])->name('das.update');
        Route::delete('/das/{das}', [DasWebController::class, 'destroy'])->name('das.destroy');

        Route::get('/wilayah/create', [WilayahSungaiWebController::class, 'create'])->name('wilayah.create');
        Route::post('/wilayah', [WilayahSungaiWebController::class, 'store'])->name('wilayah.store');
        Route::get('/wilayah/{wilayah}/edit', [WilayahSungaiWebController::class, 'edit'])->name('wilayah.edit');
        Route::put('/wilayah/{wilayah}', [WilayahSungaiWebController::class, 'update'])->name('wilayah.update');
        Route::delete('/wilayah/{wilayah}', [WilayahSungaiWebController::class, 'destroy'])->name('wilayah.destroy');

        Route::get('/staf', [StafDinasWebController::class, 'index'])->name('staf.index');
        Route::get('/staf/create', [StafDinasWebController::class, 'create'])->name('staf.create');
        Route::post('/staf', [StafDinasWebController::class, 'store'])->name('staf.store');
        Route::get('/staf/{staf}/edit', [StafDinasWebController::class, 'edit'])->name('staf.edit');
        Route::put('/staf/{staf}', [StafDinasWebController::class, 'update'])->name('staf.update');
        Route::delete('/staf/{staf}', [StafDinasWebController::class, 'destroy'])->name('staf.destroy');
    });

    // Rute berparameter didaftarkan terakhir.
    Route::get('/juru/{juru}', [JuruSungaiWebController::class, 'show'])->name('juru.show');
    Route::get('/das/{das}', [DasWebController::class, 'show'])->name('das.show');
    Route::get('/wilayah/{wilayah}', [WilayahSungaiWebController::class, 'show'])->name('wilayah.show');
});
