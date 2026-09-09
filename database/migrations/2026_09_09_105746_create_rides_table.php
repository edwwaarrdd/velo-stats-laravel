<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rides', function (Blueprint $table): void {
            $table->unsignedBigInteger('ride_id')->primary();
            $table->unsignedBigInteger('account_id');
            $table->string('status', 32);
            $table->integer('duration');
            $table->string('bike_number', 32);
            $table->string('origin_station_code', 32);
            $table->string('origin_station');
            $table->string('origin_slot_id', 16);
            $table->dateTime('checkout_time');
            $table->string('destination_station_code', 32);
            $table->string('destination_station');
            $table->string('destination_slot_id', 16);
            $table->dateTime('checkin_time');
            $table->dateTime('distance_checked_at')->nullable();
            $table->dateTime('weather_checked_at')->nullable();
            $table->timestamps();

            $table->index('checkout_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rides');
    }
};
