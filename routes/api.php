<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarangInventarisController;
use App\Http\Controllers\JenisBarangController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\PengembalianController;
use App\Http\Middleware\RoleCheck;
use Illuminate\Support\Facades\Route;

// Auth Route
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::put('/edit/{user_id}', [AuthController::class, 'update'])->middleware('auth:sanctum');
Route::put('/change-role/{user_id}', [AuthController::class, 'changeRole'])
    ->middleware(['auth:sanctum', RoleCheck::class . ':01']);

//Public Router
Route::apiResource('jenis_barang', JenisBarangController::class);
Route::apiResource('barang', BarangInventarisController::class);
Route::apiResource('peminjaman', PeminjamanController::class);
Route::apiResource('pengembalian', PengembalianController::class);