<?php

namespace Database\Factories;

use App\Models\Ride;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ride>
 */
class RideFactory extends Factory
{
    protected $model = Ride::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkoutTime = fake()->dateTimeBetween('-1 year');
        $duration = fake()->numberBetween(3, 45);

        return [
            'ride_id' => fake()->unique()->numberBetween(1, 99_999_999),
            'account_id' => 123,
            'status' => 'Completed',
            'duration' => $duration,
            'bike_number' => (string) fake()->numberBetween(1000, 9999),
            'origin_station_code' => '021',
            'origin_station' => '021- Driekoningen',
            'origin_slot_id' => (string) fake()->numberBetween(1, 30),
            'checkout_time' => $checkoutTime,
            'destination_station_code' => '041',
            'destination_station' => '041- Van Eyck',
            'destination_slot_id' => (string) fake()->numberBetween(1, 30),
            'checkin_time' => (clone $checkoutTime)->modify("+{$duration} minutes"),
            'distance_checked_at' => null,
            'weather_checked_at' => null,
        ];
    }
}
