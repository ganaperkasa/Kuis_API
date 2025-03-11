<?php

// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/registrasi', [RegisterController::class, 'store']); 
Route::get('/registrasi', [RegisterController::class, 'index']); 
Route::put('/registrasi/{id}', [RegisterController::class, 'update']); 