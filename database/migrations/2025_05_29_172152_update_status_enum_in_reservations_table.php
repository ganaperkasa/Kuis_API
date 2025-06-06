<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Di MySQL, enum tidak bisa langsung diubah, biasanya harus drop kolom lalu add ulang, atau memakai raw SQL
        // Contoh dengan raw SQL untuk MySQL:
        DB::statement("ALTER TABLE reservations MODIFY COLUMN status ENUM('pending', 'dp_paid', 'confirmed', 'canceled') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE reservations MODIFY COLUMN status ENUM('pending', 'confirmed', 'canceled') NOT NULL DEFAULT 'pending'");
    }
};
