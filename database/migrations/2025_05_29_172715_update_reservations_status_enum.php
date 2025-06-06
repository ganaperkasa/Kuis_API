<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Pastikan backup dulu database, ini akan mengubah tipe enum
        DB::statement("ALTER TABLE reservations MODIFY COLUMN status ENUM('pending', 'partially_paid', 'paid', 'confirmed', 'canceled') NOT NULL DEFAULT 'pending'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE reservations MODIFY COLUMN status ENUM('pending', 'confirmed', 'canceled') NOT NULL DEFAULT 'pending'");
    }
};
