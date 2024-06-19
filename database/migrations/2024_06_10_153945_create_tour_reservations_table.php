<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tour_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('tour_id');
            $table->foreignId('date_id');
            $table->foreignId('cost_id');
            $table->foreignId('hotel_id');
            $table->foreignId('agency_id');
            $table->decimal('total_price', 10, 2);
            $table->json('passengers');
            $table->tinyInteger('passengers_count', false, true);
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_reservations');
    }
};
