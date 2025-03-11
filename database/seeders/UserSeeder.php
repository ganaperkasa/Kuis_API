<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create admin user
        User::create([
            'name' => 'halo',
            'email' => 'gana33@gmail.com',
            'password' => Hash::make('gana1234'), 
        ]);

    }
}