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
        // Validasi inputan dari user
        $request->validate([
            'lapangan_id' => 'required|exists:lapangans,id',
            'reservation_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        // Membuat reservasi baru
        $reservation = Reservation::create([
            'user_id' => Auth::id(),
            'lapangan_id' => $request->lapangan_id,
            'reservation_date' => $request->reservation_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'status' => 'pending', // Status awal reservasi
        ]);

        return response()->json([
            'message' => 'Reservation created successfully.',
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
        // Mencari reservasi berdasarkan ID
        $reservation = Reservation::findOrFail($id);
        $reservation->status = $request->status ?? $reservation->status;
        $reservation->save();

        return response()->json(['message' => 'Reservation updated.', 'reservation' => $reservation]);
    }

    /**
     * Remove the specified reservation from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Mencari dan menghapus reservasi berdasarkan ID
        $reservation = Reservation::findOrFail($id);
        $reservation->delete();

        return response()->json(['message' => 'Reservation deleted.']);
    }
}
