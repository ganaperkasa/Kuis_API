<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    // Mendapatkan daftar reservasi
    public function index()
    {
        $reservations = Reservation::all();
        return response()->json(['data' => $reservations], 200);
    }

    // Membuat reservasi baru
    public function store(Request $request)
    {
        $request->validate([
            'lapangan_id' => 'required|exists:lapangans,id',
            'user_id' => 'required|exists:users,id',
            'tanggal' => 'required|date',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'status' => 'required|in:pending,confirmed,canceled'
        ]);

        $reservation = Reservation::create($request->all());

        return response()->json([
            'message' => 'Reservasi berhasil dibuat',
            'data' => $reservation
        ], 201);
    }

    // Mengupdate reservasi (dengan route model binding)
    public function update(Request $request, Reservation $reservation)
    {
        $request->validate([
            'lapangan_id' => 'sometimes|required|exists:lapangans,id',
            'user_id' => 'sometimes|required|exists:users,id',
            'tanggal' => 'sometimes|required|date',
            'waktu_mulai' => 'sometimes|required|date_format:H:i',
            'waktu_selesai' => 'sometimes|required|date_format:H:i|after:waktu_mulai',
            'status' => 'sometimes|required|in:pending,confirmed,canceled'
        ]);

        $reservation->update($request->only(['lapangan_id', 'user_id', 'tanggal', 'waktu_mulai', 'waktu_selesai', 'status']));

        return response()->json([
            'message' => 'Reservasi berhasil diperbarui',
            'data' => $reservation
        ], 200);
    }

    // Membatalkan reservasi (dengan route model binding)
    public function destroy(Reservation $reservation)
    {
        $reservation->delete();
        return response()->json(['message' => 'Reservasi berhasil dibatalkan'], 200);
    }
}
