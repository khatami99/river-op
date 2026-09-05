<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DasWebController;
use App\Http\Controllers\Web\JuruSungaiWebController;
use App\Http\Controllers\Web\LaporanWebController;
use App\Http\Controllers\Web\LoginController;
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

    Route::get('/laporan', [LaporanWebController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/export', [LaporanWebController::class, 'export'])->name('laporan.export');
    Route::get('/laporan/{laporan}', [LaporanWebController::class, 'show'])->name('laporan.show');

    Route::get('/juru', [JuruSungaiWebController::class, 'index'])->name('juru.index');
    Route::get('/juru/{juru}', [JuruSungaiWebController::class, 'show'])->name('juru.show');

    Route::get('/das', [DasWebController::class, 'index'])->name('das.index');
    Route::get('/das/{das}', [DasWebController::class, 'show'])->name('das.show');

    Route::get('/wilayah', [WilayahSungaiWebController::class, 'index'])->name('wilayah.index');
    Route::get('/wilayah/{wilayah}', [WilayahSungaiWebController::class, 'show'])->name('wilayah.show');
});
