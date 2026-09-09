<?php

namespace Database\Factories;

use App\Enums\TravelMode;
use App\Models\Station;
use App\Models\StationRoute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StationRoute>
 */
class StationRouteFactory extends Factory
{
    protected $model = StationRoute::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'origin_station_id' => Station::factory(),
            'destination_station_id' => Station::factory(),
            'mode' => TravelMode::Bike,
            'distance_meters' => fake()->randomFloat(1, 200, 5000),
            'duration_seconds' => fake()->randomFloat(1, 60, 1200),
        ];
    }
}
