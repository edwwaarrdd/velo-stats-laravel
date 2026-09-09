<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('station_routes', function (Blueprint $table): void {
            $table->id();
            $table->string('origin_station_id', 32);
            $table->string('destination_station_id', 32);
            $table->string('mode', 8);
            $table->float('distance_meters');
            $table->float('duration_seconds');
            $table->timestamps();

            $table->foreign('origin_station_id')
                ->references('station_id')
                ->on('stations')
                ->cascadeOnDelete();

            $table->foreign('destination_station_id')
                ->references('station_id')
                ->on('stations')
                ->cascadeOnDelete();

            $table->unique(['origin_station_id', 'destination_station_id', 'mode'], 'unique_station_route_per_mode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('station_routes');
    }
};
