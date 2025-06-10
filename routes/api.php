<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LapanganController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\DashboardAdminController;


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/register', [RegisterController::class, 'store']);
Route::post('/login', [AuthController::class, 'login']);

// Lupa Password & Reset Password
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.reset');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile/update', [ProfileController::class, 'update']);
    Route::post('/profile/upload-photo', [ProfileController::class, 'uploadPhoto']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->get('/admin/dashboard', function (Request $request) {
    return response()->json(['message' => 'Welcome, Admin!']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/dashboard', [DashboardAdminController::class, 'getdata']);
    Route::get('/dashboard/monthly-confirmed', [DashboardAdminController::class, 'monthlyConfirmedReservations']);
    Route::get('/dashboard/debug-reservations', [DashboardAdminController::class, 'debugReservations']);
});
//Lapangan
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/lapangan', [LapanganController::class, 'index']);
    Route::get('/lapangan/{id}', [LapanganController::class, 'show']);

    // Proteksi hanya untuk admin
    Route::middleware('role:admin')->group(function () {
        Route::post('/lapangan', [LapanganController::class, 'store']);
        Route::put('/lapangan/{id}', [LapanganController::class, 'update']);
        Route::delete('/lapangan/{id}', [LapanganController::class, 'destroy']);
    });
});

//Reservasi
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::put('/reservations/{id}', [ReservationController::class, 'update']);
    Route::put('/reservations/{id}/update-status', [ReservationController::class, 'updateStatus']);
    Route::delete('/reservations/{id}', [ReservationController::class, 'destroy']);
    Route::get('/reservations/check', [ReservationController::class, 'checkAvailability']);
    Route::get('/reservations/{id}', [ReservationController::class, 'show']);
    Route::put('/reservations/{id}/down-payment', [ReservationController::class, 'updateDownPayment']);
});

//Pembayaran
Route::middleware('auth:sanctum')->post('/payment/create', [PaymentController::class, 'createTransaction']);
Route::middleware('auth:sanctum')->post('/payment/reservation/{id}', [PaymentController::class, 'payReservation']);
Route::post('/midtrans/token', [MidtransController::class, 'getToken']);
Route::post('/midtrans/callback', [PaymentController::class, 'handleCallback']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/transactions', [PaymentController::class, 'index']);          // Melihat daftar transaksi
    Route::get('/transactions/{id}', [PaymentController::class, 'show']);     // Melihat detail transaksi
    Route::delete('/transactions/{id}', [PaymentController::class, 'destroy']); // Menghapus transaksi
    Route::put('/transactions/{id}/status', [PaymentController::class, 'updateStatus']); // Mengubah status transaksi
});

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::get('/users/{id}', [AdminUserController::class, 'show']);
    Route::put('/users/{id}', [AdminUserController::class, 'update']);
    Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);
});
