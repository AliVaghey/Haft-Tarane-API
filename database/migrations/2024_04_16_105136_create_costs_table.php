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
        Schema::create('costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id');
            $table->foreignId('hotel_id');
            $table->string('room_type');
            $table->decimal('one_bed', 10, 2);
            $table->decimal('two_bed', 10, 2);
            $table->decimal('plus_one', 10, 2);
            $table->decimal('cld_6', 10, 2);
            $table->decimal('cld_2', 10, 2);
            $table->decimal('baby', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('costs');
    }
};
