<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\TourStatus;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id');
            $table->string('title');
            $table->string('trip_type');
            $table->integer('expiration', false, true);
            $table->string('selling_type');
            $table->smallInteger('capacity', false, true);
            $table->json('tour_styles')->nullable();
            $table->boolean('evening_support');
            $table->boolean('midnight_support');
            $table->string('origin');
            $table->string('destination');
            $table->integer('staying_nights');
            $table->string('transportation_id')->nullable();
            $table->enum('transportation_type', ['none', 'system', 'self'])->default('none');
            $table->enum('status', TourStatus::values())->default(TourStatus::Draft);
            $table->date('start')->nullable();
            $table->date('end')->nullable();
            $table->json('hotels')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};