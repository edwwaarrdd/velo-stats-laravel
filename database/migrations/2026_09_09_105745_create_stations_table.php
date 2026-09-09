<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stations', function (Blueprint $table): void {
            $table->string('station_id', 32)->primary();
            $table->string('name');
            $table->string('short_name', 32);
            $table->float('lat');
            $table->float('lon');
            $table->string('address');
            $table->string('post_code', 16);
            $table->json('rental_methods');
            $table->integer('capacity')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stations');
    }
};
