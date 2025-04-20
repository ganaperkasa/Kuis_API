<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('lapangans', function (Blueprint $table) {
            // Rename kolom
            $table->renameColumn('nama', 'name');
            $table->renameColumn('deskripsi', 'description');
            $table->renameColumn('harga_per_jam', 'price');

            // Hapus kolom lama
            $table->dropColumn('tersedia');

            // Tambah kolom baru
            $table->string('location')->after('price');
            $table->string('photo')->nullable()->after('location');
            $table->integer('capacity')->after('photo');
            $table->enum('type', ['Futsal', 'Badminton', 'Basket', 'Tennis', 'Voli'])->after('capacity');
            $table->enum('status', ['available', 'booked'])->default('available')->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('lapangans', function (Blueprint $table) {
            // Balik perubahan
            $table->renameColumn('name', 'nama');
            $table->renameColumn('description', 'deskripsi');
            $table->renameColumn('price', 'harga_per_jam');

            $table->boolean('tersedia')->default(true);

            $table->dropColumn([
                'location',
                'photo',
                'capacity',
                'type',
                'status'
            ]);
        });
    }
};
