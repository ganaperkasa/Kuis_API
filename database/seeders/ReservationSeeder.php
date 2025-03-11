<?php

namespace Database\Seeders;

use App\Models\Reservation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    public function run()
    {
        Reservation::truncate(); // Menghapus data lama

        Reservation::insert([
            [
                'lapangan_id' => 1, // ID lapangan yang valid
                'user_id' => 1, // ID user yang valid
                'tanggal' => '2025-03-15', // Format tanggal benar
                'waktu_mulai' => '14:00', // Format waktu benar
                'waktu_selesai' => '16:00', // Format waktu benar
                'status' => 'pending', // Status sebagai string
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
