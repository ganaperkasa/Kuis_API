<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->renameColumn('tanggal', 'reservation_date');
            $table->renameColumn('waktu_mulai', 'start_time');
            $table->renameColumn('waktu_selesai', 'end_time');
        });

        // Modifikasi enum pakai DB::statement()
        DB::statement("ALTER TABLE reservations MODIFY status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending'");
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->renameColumn('reservation_date', 'tanggal');
            $table->renameColumn('start_time', 'waktu_mulai');
            $table->renameColumn('end_time', 'waktu_selesai');
        });

        DB::statement("ALTER TABLE reservations MODIFY status ENUM('pending', 'confirmed', 'canceled') DEFAULT 'pending'");
    }
};
