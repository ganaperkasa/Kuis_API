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

    public function createTransaction(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $orderId = uniqid();

        Transaction::create([
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

    public function payReservation(Request $request, $id)
    {
        $reservation = Reservation::with('lapangan', 'user')->findOrFail($id);

        // Hanya status booked dan partially_paid yang bisa dibayar
        if (!in_array($reservation->status, ['booked', 'partially_paid'])) {
            return response()->json(['error' => 'Reservasi tidak valid untuk dibayar.'], 400);
        }

        $jam = Carbon::parse($reservation->end_time)->diffInHours(Carbon::parse($reservation->start_time));
        $totalAmount = $reservation->lapangan->harga * $jam;

        // Ambil jenis pembayaran: dp / full / pelunasan
        $paymentType = $request->input('payment_type', 'dp'); // default DP
        $amount = 0;

        if ($paymentType === 'dp') {
            $amount = $totalAmount * 0.3;
        } elseif ($paymentType === 'full') {
            $amount = $totalAmount;
        } elseif ($paymentType === 'pelunasan') {
            // Cari transaksi DP sebelumnya yang sudah sukses
            $dpTransaction = Transaction::where('reservation_id', $reservation->id)
                ->where('payment_type', 'dp')
                ->where('status', 'success')
                ->first();

            if (!$dpTransaction) {
                return response()->json(['error' => 'Pelunasan tidak valid tanpa pembayaran DP sebelumnya.'], 400);
            }

            $amount = $totalAmount - $dpTransaction->amount;
            if ($amount <= 0) {
                return response()->json(['error' => 'Jumlah pelunasan tidak valid.'], 400);
            }
        } else {
            return response()->json(['error' => 'Tipe pembayaran tidak valid.'], 400);
        }

        $transaction = Transaction::create([
            'user_id' => $reservation->user_id,
            'reservation_id' => $reservation->id,
            'order_id' => 'RESV-' . $reservation->id . '-' . time(),
            'amount' => $amount,
            'status' => 'pending',
            'payment_type' => $paymentType,
        ]);

        $params = [
            'transaction_details' => [
                'order_id' => $transaction->order_id,
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

        if ($transactionStatus === 'settlement') {
            $transaction->status = 'success';

            if ($transaction->reservation_id) {
                $reservation = Reservation::find($transaction->reservation_id);
                if ($reservation) {
                    // Cek status booking sebelum update
                    $prevStatus = $reservation->status;

                    if ($transaction->payment_type === 'full') {
                        $reservation->status = 'paid';
                    } elseif ($transaction->payment_type === 'dp') {
                        // Jangan turunkan status jika sudah lunas
                        if ($reservation->status !== 'paid') {
                            $reservation->status = 'partially_paid';
                        }
                    } elseif ($transaction->payment_type === 'pelunasan') {
                        $reservation->status = 'paid';
                    }

                    $reservation->save();

                    Log::info('Reservation status updated', [
                        'reservation_id' => $reservation->id,
                        'status_before' => $prevStatus,
                        'status_after' => $reservation->status,
                        'payment_type' => $transaction->payment_type,
                    ]);
                }
            }
        } elseif (in_array($transactionStatus, ['pending', 'capture'])) {
            $transaction->status = 'pending';
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            $transaction->status = 'failed';
        }

        $transaction->save();

        Log::info('Transaction status updated', [
            'order_id' => $orderId,
            'status' => $transaction->status,
            'payment_type' => $transaction->payment_type,
        ]);

        return response()->json(['message' => 'Callback processed']);
    }

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

    public function show($id)
    {
        return Transaction::with('user')->findOrFail($id);
    }

    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->delete();

        return response()->json(['message' => 'Transaction deleted.']);
    }

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
