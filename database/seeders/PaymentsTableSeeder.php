<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PaymentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('payments')->insert([
            [
                'reservation_id' => 2,
                'amount' => 50000.00,
                'payment_type' => 'DP',
                'status' => 'success',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
