<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\RekognisiController;
use App\Http\Controllers\AdminController;

// baru login admin,belom login dosen
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});
Route::prefix('admin')->group(function () {
    // auth
    Route::post('/login', [AdminController::class, 'login']);
    Route::post('/logout', [AdminController::class, 'logout'])->middleware('auth:sanctum');

    // Profile
    Route::get('/profile', [AdminController::class, 'profile'])->middleware('auth:sanctum');
    // Dosen
    Route::get('/dosen-all', [AdminController::class, 'index']);
    Route::post('/dosen-add', [AdminController::class, 'store']);
    Route::put('/dosen/update/{dosen}', [AdminController::class, 'update']);
    Route::delete('/dosen/delete/{dosen}', [AdminController::class, 'destroy']);
    Route::get('/dosen/prodi/{prodi}', [AdminController::class, 'dosenPerProdi']);
    Route::get('/dosen/search', [AdminController::class, 'search']); //dosen/search?nama=zalpe
    // Rekognisi
    Route::get('/rekognisi', [AdminController::class, 'indexRekognisi'])->middleware('auth:sanctum');
    Route::get('/rekognisi/kategori', [AdminController::class, 'rekognisiByKategori']);
    Route::get('/rekognisi/show/{id}', [AdminController::class, 'showRekognisi']);
    Route::post('/rekognisi/tolak/{id}', [AdminController::class, 'tolakRekognisi']);
    Route::post('/rekognisi/delete/{id}', [AdminController::class, 'hapusRekognisi']);
    Route::post('/rekognisi/terima/{id}', [AdminController::class, 'terimaRekognisi']);

    // Dashboard
    Route::get('/rekognisi/dashboard-statistik', [AdminController::class, 'dashboardStatistik']);
    Route::get('/rekognisi/dashboard-grafik', [AdminController::class, 'dashboardGrafik']);
});

Route::prefix('dosen')->group(function () {
    Route::post('/login', [DosenController::class, 'login']);
    Route::post('/logout', [DosenController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/dashboard-statistik/{id}', [DosenController::class, 'dashboardStatistik'])->middleware('auth:sanctum');
    Route::get('/riwayat-aktivitas/{id}', [DosenController::class, 'riwayatAktivitas'])->middleware('auth:sanctum');
    Route::get('/rekognisi-saya/{id}', [DosenController::class, 'rekognisiSaya']);

});


// Route::post('/rekognisi', action: [RekognisiController::class, 'store']); ->dosen gabisa insert;
// Route::post('/rekognisi', action: [RekognisiController::class, 'store'])->middleware('auth:sanctum');
Route::put('/rekognisi/update/{id}', [RekognisiController::class, 'update']);
Route::post('/rekognisi/umpan-balik/{id}', [RekognisiController::class, 'beriUmpanBalik']);
Route::get('/rekognisi/perlu-validasi', [RekognisiController::class, 'rekognisiPerluValidasi']);

// Udh Fix 

Route::post('/rekognisi/komentar', [RekognisiController::class, 'storeKomentar']);
// Route::post('/rekognisi/file/upload', [RekognisiFileController::class, 'store']); admin gaboleh








