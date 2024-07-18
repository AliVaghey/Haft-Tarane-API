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
        Schema::table('price_changes', function (Blueprint $table) {
            $table->decimal('one_bed', 10, 2)->default(0);
            $table->decimal('two_bed', 10, 2)->default(0);
            $table->decimal('plus_one', 10, 2)->default(0);
            $table->decimal('cld_6', 10, 2)->default(0);
            $table->decimal('cld_2', 10, 2)->default(0);
            $table->decimal('baby', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
