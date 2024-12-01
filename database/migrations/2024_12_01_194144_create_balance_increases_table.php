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
        Schema::create('balance_increases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id');
            $table->decimal('amount', 10, 0);
            $table->enum('status', ['paid', 'pending', 'canceled'])->default('pending');
            $table->string('transaction_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balance_increases');
    }
};
