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
            $table->float('one_bed');
            $table->float('two_bed');
            $table->float('plus_one');
            $table->float('cld_6');
            $table->float('cld_2');
            $table->float('baby');
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
