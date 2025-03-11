<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lapangan;
use Illuminate\Support\Facades\DB;

class LapanganSeeder extends Seeder
{
    public function run()
    {
        DB::table('lapangans')->truncate(); // Menghapus data lama

        Lapangan::insert([
            [
                'nama' => 'Lapangan Baru',
                'deskripsi' => 'Lapangan dengan rumput beton',
                'harga_per_jam' => 75000,
                'tersedia' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
