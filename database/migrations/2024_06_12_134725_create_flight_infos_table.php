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
        Schema::create('flight_infos', function (Blueprint $table) {
            $table->id();
            $table->string('uniqueID')->nullable();
            $table->string('type')->nullable();
            $table->decimal("price_final", 12, 2)->nullable();
            $table->decimal("price_final_chd", 12, 2)->nullable();
            $table->decimal("price_final_inf", 12, 2)->nullable();
            $table->decimal("price_final_fare", 12, 2)->nullable();
            $table->decimal("price_final_chd_fare", 12, 2)->nullable();
            $table->decimal("price_final_inf_fare", 12, 2)->nullable();
            $table->tinyInteger("capacity")->default(1);
            $table->string("from")->nullable();
            $table->string("to")->nullable();
            $table->string("number_flight")->nullable();
            $table->string("type_flight")->nullable();
            $table->string("carrier")->nullable();
            $table->date("date_flight")->nullable();
            $table->string("time_flight")->nullable();
            $table->string("airline")->nullable();
            $table->string("IATA_code")->nullable();
            $table->string("cabinclass")->nullable();
            $table->string("SellingType")->nullable();
            $table->string("weelchairsupport")->nullable();
            $table->string("price_Markup")->nullable();
            $table->string("Share_Sale")->nullable();
            $table->string("has_stop")->nullable();
            $table->string("alarm_msg")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_infos');
    }
};
