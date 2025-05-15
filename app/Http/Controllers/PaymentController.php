<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Snap;
use Midtrans\Config;
use Midtrans\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;

class PaymentController extends Controller
{
    public function __construct()
    {
        // Inisialisasi konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.sanitized');
        Config::$is3ds = config('midtrans.3ds');
    }

    // Fungsi untuk membuat transaksi baru
    public function createTransaction(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $orderId = uniqid();

        // Simpan transaksi awal dengan status pending
        Transaction::create([
            'user_id' => $user->id,
            'order_id' => $orderId,
            'amount' => $request->amount,
            'status' => 'pending',
        ]);

        // Set parameter untuk Midtrans Snap
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $request->amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ]
        ];

        // Mendapatkan token untuk transaksi Snap Midtrans
        $snapToken = Snap::getSnapToken($params);

        return response()->json(['token' => $snapToken]);
    }

    // Fungsi untuk menangani callback dari Midtrans
    public function handleCallback(Request $request)
    {
        // Log untuk debugging
        Log::info('Midtrans Callback Received', ['request' => $request->all()]);

        $notif = new Notification();
        $transactionStatus = $notif->transaction_status;
        $orderId = $notif->order_id;

        // Mencari transaksi berdasarkan order_id
        $transaction = Transaction::where('order_id', $orderId)->first();

        if (!$transaction) {
            Log::error('Transaction not found', ['order_id' => $orderId]);
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        // Menyesuaikan status transaksi berdasarkan status dari Midtrans
        if ($transactionStatus === 'settlement') {
            $transaction->status = 'success';
        } elseif (in_array($transactionStatus, ['pending', 'capture'])) {
            $transaction->status = 'pending';
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            $transaction->status = 'failed';
        }

        // Simpan perubahan status transaksi
        $transaction->save();

        Log::info('Transaction status updated', ['order_id' => $orderId, 'status' => $transaction->status]);

        return response()->json(['message' => 'Callback processed']);
    }

    // Fungsi untuk menampilkan daftar transaksi berdasarkan peran pengguna
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            // Admin bisa melihat semua transaksi
            return Transaction::with('user')->get();
        }

        // User biasa hanya melihat transaksi miliknya sendiri
        return Transaction::with('user')
            ->where('user_id', $user->id)
            ->get();
    }

    // Fungsi untuk menampilkan detail transaksi
    public function show($id)
    {
        return Transaction::with('user')->findOrFail($id);
    }

    // Fungsi untuk menghapus transaksi
    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->delete();

        return response()->json(['message' => 'Transaction deleted.']);
    }

    // Fungsi untuk memperbarui status transaksi
    public function updateStatus(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        
        // Validasi status yang diperbolehkan
        $validated = $request->validate([
            'status' => 'required|in:pending,success,failed',
        ]);
        
        // Perbarui status transaksi
        $transaction->status = $validated['status'];
        $transaction->save();

        return response()->json(['message' => 'Status transaksi berhasil diperbarui']);
    }
}
