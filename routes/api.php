<?php

// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LapanganController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ReservationController;


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

//Reservasi
Route::get('/reservations', [ReservationController::class, 'index']);
Route::post('/reservations', [ReservationController::class, 'store']);
Route::put('/reservations/{id}', [ReservationController::class, 'update']);
Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);