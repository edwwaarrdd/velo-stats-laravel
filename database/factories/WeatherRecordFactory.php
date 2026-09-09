<?php

namespace Database\Factories;

use App\Domain\Rides\Models\Ride;
use App\Domain\Weather\Models\WeatherRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WeatherRecord>
 */
class WeatherRecordFactory extends Factory
{
    protected $model = WeatherRecord::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ride_id' => Ride::factory(),
            'temperature_c' => fake()->randomFloat(1, -5, 35),
            'apparent_temperature_c' => fake()->randomFloat(1, -10, 38),
            'precipitation_mm' => fake()->randomFloat(1, 0, 10),
            'rain_mm' => fake()->randomFloat(1, 0, 10),
            'snowfall_cm' => 0.0,
            'cloud_cover_percent' => fake()->randomFloat(1, 0, 100),
            'wind_speed_kmh' => fake()->randomFloat(1, 0, 60),
            'wind_gusts_kmh' => fake()->randomFloat(1, 0, 90),
            'wind_direction_degrees' => fake()->randomFloat(1, 0, 359),
            'relative_humidity_percent' => fake()->randomFloat(1, 20, 100),
            'weather_code' => fake()->randomElement([0, 1, 2, 3, 61, 63, 80]),
            'observed_at' => fake()->dateTimeBetween('-1 year'),
        ];
    }
}
