<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_records', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('ride_id')->unique();
            $table->float('temperature_c');
            $table->float('apparent_temperature_c');
            $table->float('precipitation_mm');
            $table->float('rain_mm');
            $table->float('snowfall_cm');
            $table->float('cloud_cover_percent');
            $table->float('wind_speed_kmh');
            $table->float('wind_gusts_kmh');
            $table->float('wind_direction_degrees');
            $table->float('relative_humidity_percent');
            $table->integer('weather_code');
            $table->dateTime('observed_at');
            $table->timestamps();

            $table->foreign('ride_id')
                ->references('ride_id')
                ->on('rides')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_records');
    }
};
