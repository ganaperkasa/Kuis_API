<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Snap;
use Midtrans\Config;
use Midtrans\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;
use App\Models\Reservation;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.sanitized');
        Config::$is3ds = config('midtrans.3ds');
    }

    /**
     * Membuat transaksi pembayaran umum (bukan reservasi).
     * Pastikan penggunaan sesuai kebutuhan atau dihilangkan jika tidak diperlukan.
     */
    public function createTransaction(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();

        // Validasi input amount
        $request->validate([
            'amount' => 'required|numeric|min:1000', // minimal 1000 atau sesuai kebijakan
        ]);

        $orderId = uniqid('TXN-');

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'order_id' => $orderId,
            'amount' => $request->amount,
            'status' => 'pending',
        ]);

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

        $snapToken = Snap::getSnapToken($params);
        return response()->json(['token' => $snapToken]);
    }

    /**
     * Membuat transaksi pembayaran berdasarkan reservasi.
     * Hanya user pemilik reservasi dan reservasi dengan status yang valid dapat membayar.
     */
    public function payReservation($id)
    {
        $reservation = Reservation::with('lapangan', 'user')->findOrFail($id);

        // Pastikan user adalah pemilik reservasi
        if ($reservation->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Cek status reservasi, misal bisa bayar jika status 'pending' atau 'booked'
        if (!in_array($reservation->status, ['pending', 'booked'])) {
            return response()->json(['error' => 'Reservasi tidak valid untuk dibayar.'], 400);
        }

        // Gunakan harga total dari reservasi yang sudah dihitung dan disimpan
        $amount = $reservation->price; 

        // Membuat order_id unik untuk transaksi reservasi
        $orderId = 'RESV-' . $reservation->id . '-' . time();

        $transaction = Transaction::create([
            'user_id' => $reservation->user_id,
            'reservation_id' => $reservation->id,
            'order_id' => $orderId,
            'amount' => $amount,
            'status' => 'pending',
        ]);

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $reservation->user->name,
                'email' => $reservation->user->email,
            ]
        ];

        $snapToken = Snap::getSnapToken($params);
        return response()->json(['token' => $snapToken]);
    }

    /**
     * Callback handler dari Midtrans untuk update status transaksi dan reservasi
     */
    public function handleCallback(Request $request)
    {
        Log::info('Midtrans Callback Received', ['request' => $request->all()]);
        
        $notif = new Notification();

        $transactionStatus = $notif->transaction_status;
        $orderId = $notif->order_id;

        $transaction = Transaction::where('order_id', $orderId)->first();

        if (!$transaction) {
            Log::error('Transaction not found', ['order_id' => $orderId]);
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        // Update status transaksi berdasarkan status Midtrans
        if ($transactionStatus === 'settlement') {
            $transaction->status = 'success';

            // Jika transaksi terkait reservasi, update status reservasi juga
            if ($transaction->reservation_id) {
                $reservation = Reservation::find($transaction->reservation_id);
                if ($reservation) {
                    $reservation->status = 'paid';
                    $reservation->save();
                }
            }
        } elseif (in_array($transactionStatus, ['pending', 'capture'])) {
            $transaction->status = 'pending';
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            $transaction->status = 'failed';
        }

        $transaction->save();

        Log::info('Transaction status updated', ['order_id' => $orderId, 'status' => $transaction->status]);
        return response()->json(['message' => 'Callback processed']);
    }

    /**
     * Daftar semua transaksi user (atau admin semua transaksi)
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return Transaction::with('user')->get();
        }

        return Transaction::with('user')
            ->where('user_id', $user->id)
            ->get();
    }

    /**
     * Detail transaksi berdasarkan ID
     */
    public function show($id)
    {
        return Transaction::with('user')->findOrFail($id);
    }

    /**
     * Hapus transaksi berdasarkan ID
     */
    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->delete();

        return response()->json(['message' => 'Transaction deleted.']);
    }

    /**
     * Update status transaksi secara manual
     */
    public function updateStatus(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,success,failed',
        ]);

        $transaction->status = $validated['status'];
        $transaction->save();

        return response()->json(['message' => 'Status transaksi berhasil diperbarui']);
    }
}
