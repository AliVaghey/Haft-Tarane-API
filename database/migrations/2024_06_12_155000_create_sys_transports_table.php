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
        Schema::create('sys_transports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_id')->nullable();
            $table->foreignId('tour_id');
            $table->foreignId('date_id')->nullable();
            $table->boolean('returning')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sys_transports');
    }
};
