<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('reservations', function (Blueprint $table) {
        $table->boolean('is_dp')->default(false);
        $table->integer('dp_amount')->nullable();
        $table->integer('full_price')->default(0);
    });
}

public function down()
{
    Schema::table('reservations', function (Blueprint $table) {
        $table->dropColumn(['is_dp', 'dp_amount', 'full_price']);
    });
}
};
