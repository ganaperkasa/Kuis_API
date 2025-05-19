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
        ]);

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
        $reservation->status = $request->status ?? $reservation->status;
        $reservation->save();

        return response()->json(['message' => 'Reservasi diperbarui.', 'reservation' => $reservation]);
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

    public function show($id)
{
    $reservation = Reservation::with('lapangan')->findOrFail($id);
    return response()->json([
        'id' => $reservation->id,
        'lapangan_name' => $reservation->lapangan->name,
        'reservation_date' => $reservation->reservation_date,
        'start_time' => $reservation->start_time,
        'end_time' => $reservation->end_time,
        'price' => $reservation->price,
        'status' => $reservation->status,
    ]);
}

}
