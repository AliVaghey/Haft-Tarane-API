<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plane_tickts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->foreignId('flight_info_id');
            $table->json('passengers');
            $table->json('reservation_results');
            $table->decimal('total_price', 10, 0);
            $table->json('buy_ticket_results')->nullable();
            $table->enum('status', ['paid', 'pending', 'canceled'])->default('pending');
            $table->string('voucher')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plane_tickts');
    }
};
