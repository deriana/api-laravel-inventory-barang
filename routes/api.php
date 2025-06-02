<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarangInventarisController;
use App\Http\Controllers\JenisBarangController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\PengembalianController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\RoleCheck; // Pastikan middleware diimpor dengan namespace lengkap
use Illuminate\Support\Facades\Route;

// Auth Route
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::put('/edit/{user_id}', [AuthController::class, 'update'])->middleware('auth:sanctum');
Route::put('/change-role/{user_id}', [AuthController::class, 'changeRole'])
    ->middleware(['auth:sanctum', RoleCheck::class . ':Ad']);

// // Public Routes (bisa diakses siapa saja)
Route::apiResource('jenis_barang', JenisBarangController::class);
Route::apiResource('barang', BarangInventarisController::class);
Route::apiResource('peminjaman', PeminjamanController::class);
Route::apiResource('pengembalian', PengembalianController::class);
Route::apiResource('user', UserController::class);

// Laporan Routes - Hanya Admin dan Super Admin yang bisa mengakses
Route::middleware(['auth:sanctum', RoleCheck::class . ':Ad'])->group(function () {
    Route::get('laporan/daftar-barang', [LaporanController::class, 'laporanDaftarBarang']);
    Route::get('laporan/status-barang', [LaporanController::class, 'laporanStatusBarang']);
    Route::get('laporan/pengembalian-barang', [LaporanController::class, 'laporanPengembalianBarang']);
});

// Admin Routes - hanya untuk admin dan super admin
Route::middleware(['auth:sanctum', RoleCheck::class . ':Ad'])->group(function () {
    Route::apiResource('jenis_barang', JenisBarangController::class);
    Route::apiResource('user', UserController::class);
});

// Pengaturan akses barang dan peminjaman - untuk User dan Admin
Route::middleware(['auth:sanctum', RoleCheck::class . ':Us|Ad'])->group(function () {
    Route::apiResource('barang', BarangInventarisController::class);
    Route::apiResource('peminjaman', PeminjamanController::class);
    Route::apiResource('pengembalian', PengembalianController::class);
});
