<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MidtransController extends Controller
{
    public function getToken(Request $request)
    {
        // Validasi input
        $request->validate([
            'reservation_id' => 'required|integer',
        ]);

        $reservationId = $request->input('reservation_id');

        // TODO: Logika integrasi Midtrans untuk generate payment token
        // Contoh dummy redirect URL (ganti dengan URL asli Midtrans payment page)
        $redirectUrl = "https://payment.midtrans.com/redirect?reservation_id=" . $reservationId;

        return response()->json([
            'redirect_url' => $redirectUrl
        ]);
    }
}
