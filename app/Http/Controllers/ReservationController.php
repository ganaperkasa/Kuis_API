<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    /**
     * Display a listing of the reservations.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Mengambil reservasi berdasarkan role pengguna
        $reservations = $user->role === 'admin'
            ? Reservation::with('lapangan', 'user')->get()
            : $user->reservations()->with('lapangan')->get(); // Menggunakan relasi reservations()

        return response()->json($reservations);
    }

    /**
     * Store a newly created reservation in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'lapangan_id' => 'required|exists:lapangans,id',
            'reservation_date' => 'required|date|after:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'price' => 'required|integer|min:0',
            'is_dp' => 'sometimes|boolean',
            'dp_amount' => 'nullable|integer|min:0',
        ]);

        // Jika DP aktif, dp_amount wajib dan harus <= price
        if (($request->is_dp ?? false) && (is_null($request->dp_amount) || $request->dp_amount > $request->price)) {
            return response()->json(['message' => 'Jumlah DP harus diisi dan tidak boleh lebih besar dari harga total.'], 422);
        }

        $existingReservation = Reservation::where('lapangan_id', $request->lapangan_id)
            ->where('reservation_date', $request->reservation_date)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                      ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                      ->orWhere(function ($query) use ($request) {
                          $query->where('start_time', '<', $request->start_time)
                                ->where('end_time', '>', $request->end_time);
                      });
            })->exists();

        if ($existingReservation) {
            return response()->json(['message' => 'Waktu yang dipilih sudah terpesan.'], 400);
        }

        $reservation = Reservation::create([
            'user_id' => Auth::id(),
            'lapangan_id' => $request->lapangan_id,
            'reservation_date' => $request->reservation_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'full_price' => $request->price,
            'is_dp' => $request->is_dp ?? false,
            'dp_amount' => $request->dp_amount ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Reservasi berhasil dibuat.',
            'reservation' => $reservation
        ]);
    }

    /**
     * Update the specified reservation.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $reservation = Reservation::findOrFail($id);

        // Update status jika ada
        if ($request->has('status')) {
            $reservation->status = $request->status;
        }

        // Update DP info jika ada
        if ($request->has('is_dp')) {
            $reservation->is_dp = $request->is_dp;
        }
        if ($request->has('dp_amount')) {
            // Validasi dp_amount tidak boleh lebih besar dari full_price
            if ($request->dp_amount > $reservation->full_price) {
                return response()->json(['message' => 'Jumlah DP tidak boleh lebih besar dari harga total.'], 422);
            }
            $reservation->dp_amount = $request->dp_amount;
        }

        $reservation->save();

        return response()->json(['message' => 'Reservasi diperbarui.', 'reservation' => $reservation]);
    }

public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:pending,confirmed,partially_paid,cancelled',
        'amount' => 'nullable|numeric',
    ]);

    $reservation = Reservation::findOrFail($id);

    // Jika ada nilai pembayaran (amount), cek apakah DP atau Full
    if ($request->has('amount') && $reservation->price) {
        if ($request->amount < $reservation->price) {
            $reservation->status = 'partially_paid';
        } else {
            $reservation->status = 'confirmed';
        }
    } else {
        // Pakai status dari input jika tidak mengirim amount
        $reservation->status = $request->status;
    }

    $reservation->save();

    return response()->json([
        'message' => 'Status updated successfully',
        'reservation' => $reservation,
    ]);
}

    /**
     * Remove the specified reservation from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $reservation = Reservation::findOrFail($id);
        $reservation->delete();

        return response()->json(['message' => 'Reservasi dihapus.']);
    }

    /**
     * Check availability of a lapangan on a given date and time.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'lapangan_id' => 'required|exists:lapangans,id',
            'reservation_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        $conflict = Reservation::where('lapangan_id', $request->lapangan_id)
            ->where('reservation_date', $request->reservation_date)
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                      ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                      ->orWhere(function ($query) use ($request) {
                          $query->where('start_time', '<', $request->start_time)
                                ->where('end_time', '>', $request->end_time);
                      });
            })->exists();

        if ($conflict) {
            return response()->json(['available' => false, 'message' => 'Waktu sudah terisi.']);
        }

        return response()->json(['available' => true, 'message' => 'Waktu tersedia.']);
    }

    /**
     * Display the specified reservation detail.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
public function show($id)
{
    $reservation = Reservation::with('lapangan')->findOrFail($id);

    // Tentukan harga yang harus dibayar (price) berdasarkan apakah menggunakan DP atau tidak
    $price = $reservation->is_dp ? $reservation->dp_amount : $reservation->full_price;

    return response()->json([
        'id' => $reservation->id,
        'lapangan_name' => $reservation->lapangan->name,
        'reservation_date' => $reservation->reservation_date,
        'start_time' => $reservation->start_time,
        'end_time' => $reservation->end_time,
        'full_price' => $reservation->full_price,
        'is_dp' => $reservation->is_dp,
        'dp_amount' => $reservation->dp_amount,
        'price' => $price,  // <- harga yang harus dibayar saat ini
        'status' => $reservation->status,
    ]);
}
}
