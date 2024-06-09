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
        Schema::create('transportations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained('tours');
            $table->tinyInteger('sort', false, true)->default(1);
            $table->string('type');
            $table->string('origin');
            $table->string('destination');
            $table->dateTime('start');
            $table->dateTime('end');
            $table->string('price');
            $table->string('duration')->nullable();
            $table->string('company_name')->nullable();
            $table->string('transportation_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transportaions');
    }
};
