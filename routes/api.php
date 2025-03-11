<?php

// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LapanganController;
use App\Http\Controllers\RegisterController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/registrasi', [RegisterController::class, 'store']);
Route::get('/registrasi', [RegisterController::class, 'index']);
Route::put('/registrasi/{id}', [RegisterController::class, 'update']);

//Lapangan
Route::get('/fields', [LapanganController::class, 'index']); // Mendapatkan daftar lapangan
Route::post('/fields', [LapanganController::class, 'store']); // Menambahkan lapangan baru
Route::put('/fields/{id}', [LapanganController::class, 'update']); // Mengupdate informasi lapangan
Route::delete('/fields/{id}', [LapanganController::class, 'destroy']); // Menghapus lapangan
