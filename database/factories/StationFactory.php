<?php

namespace Database\Factories;

use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Station>
 */
class StationFactory extends Factory
{
    protected $model = Station::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $number = fake()->unique()->numberBetween(1, 999);

        return [
            'station_id' => str_pad((string) $number, 3, '0', STR_PAD_LEFT),
            'name' => str_pad((string) $number, 3, '0', STR_PAD_LEFT).'- '.fake()->streetName(),
            'short_name' => str_pad((string) $number, 3, '0', STR_PAD_LEFT),
            'lat' => fake()->latitude(51.15, 51.30),
            'lon' => fake()->longitude(4.35, 4.50),
            'address' => fake()->streetAddress(),
            'post_code' => (string) fake()->numberBetween(2000, 2660),
            'rental_methods' => ['KEY', 'TRANSITCARD'],
            'capacity' => fake()->numberBetween(10, 40),
        ];
    }
}
