<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\LaporanController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/auth/login', [AuthController::class, 'login']);

// 'juru.aktif' berjaga di belakang 'auth:sanctum': token yang sah pun ditolak
// bila akunnya sudah dinonaktifkan dinas. Logout sengaja ikut dijaga — juru
// nonaktif tetap boleh keluar, dan itu ditangani aplikasi sebagai sesi habis.
Route::middleware(['auth:sanctum', 'juru.aktif'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/master/sync', [MasterController::class, 'sync']);
    Route::get('/master/profil', [MasterController::class, 'profil']);
    Route::post('/laporan/sync', [LaporanController::class, 'sync']);
    Route::post('/laporan/upload-foto', [LaporanController::class, 'uploadFoto']);
    Route::post('/sinkronisasi/status', [\App\Http\Controllers\StatusSinkronisasiController::class, 'lapor']);
});
