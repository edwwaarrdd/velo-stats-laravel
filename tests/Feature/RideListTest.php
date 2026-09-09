<?php

use App\Enums\TravelMode;
use App\Models\Ride;
use App\Models\Station;
use App\Models\StationRoute;
use App\Models\WeatherRecord;

function ride(array $attributes = []): Ride
{
    return Ride::factory()->create($attributes);
}

function bikeRouteBetween(
    string $originCode,
    string $destinationCode,
    float $distanceMeters,
    float $durationSeconds = 300.0,
): StationRoute {
    Station::factory()->create(['station_id' => $originCode]);
    Station::factory()->create(['station_id' => $destinationCode]);

    return StationRoute::factory()->create([
        'origin_station_id' => $originCode,
        'destination_station_id' => $destinationCode,
        'mode' => TravelMode::Bike,
        'distance_meters' => $distanceMeters,
        'duration_seconds' => $durationSeconds,
    ]);
}

it('returns an empty result list when there are no rides', function (): void {
    $this->getJson('/rides')->assertOk()->assertExactJson(['results' => []]);
});

it('returns rides most recent first', function (): void {
    ride(['ride_id' => 1, 'checkout_time' => '2026-01-01 08:00:00']);
    ride(['ride_id' => 2, 'checkout_time' => '2026-03-01 08:00:00']);
    ride(['ride_id' => 3, 'checkout_time' => '2026-02-01 08:00:00']);

    $response = $this->getJson('/rides');

    expect(array_column($response->json('results'), 'ride_id'))->toBe([2, 3, 1]);
});

it('returns every ride field, with distance, speed, expected ride time and weather', function (): void {
    bikeRouteBetween('021', '041', 1500.0, 400.0);

    $ride = ride([
        'ride_id' => 73147208,
        'account_id' => 123,
        'status' => 'Completed',
        'duration' => 10,
        'bike_number' => '5097',
        'origin_station_code' => '021',
        'origin_station' => '021- Driekoningen',
        'origin_slot_id' => '15',
        'checkout_time' => '2026-09-06 08:57:02',
        'destination_station_code' => '041',
        'destination_station' => '041- Van Eyck',
        'destination_slot_id' => '23',
        'checkin_time' => '2026-09-06 09:05:30',
    ]);

    WeatherRecord::factory()->create([
        'ride_id' => $ride->ride_id,
        'temperature_c' => 18.0,
        'apparent_temperature_c' => 17.1,
        'precipitation_mm' => 0.0,
        'rain_mm' => 0.0,
        'snowfall_cm' => 0.0,
        'cloud_cover_percent' => 42.0,
        'wind_speed_kmh' => 11.2,
        'wind_gusts_kmh' => 24.5,
        'wind_direction_degrees' => 210.0,
        'relative_humidity_percent' => 68.0,
        'weather_code' => 3,
        'observed_at' => '2026-09-06 09:00:00',
    ]);

    $response = $this->getJson('/rides');

    $response->assertOk()->assertExactJson([
        'results' => [
            [
                'ride_id' => 73147208,
                'account_id' => 123,
                'status' => 'Completed',
                'duration' => 10,
                'bike_number' => '5097',
                'origin_station_code' => '021',
                'origin_station' => '021- Driekoningen',
                'origin_slot_id' => '15',
                'checkout_time' => '2026-09-06T08:57:02Z',
                'destination_station_code' => '041',
                'destination_station' => '041- Van Eyck',
                'destination_slot_id' => '23',
                'checkin_time' => '2026-09-06T09:05:30Z',
                'distance_meters' => 1500.0,
                'speed_kmh' => 9.0,
                'expected_duration_seconds' => 400.0,
                'actual_duration_seconds' => 508.0,
                'duration_vs_expected_seconds' => 108.0,
                'weather' => [
                    'temperature_c' => 18.0,
                    'apparent_temperature_c' => 17.1,
                    'precipitation_mm' => 0.0,
                    'rain_mm' => 0.0,
                    'snowfall_cm' => 0.0,
                    'cloud_cover_percent' => 42.0,
                    'wind_speed_kmh' => 11.2,
                    'wind_gusts_kmh' => 24.5,
                    'wind_direction_degrees' => 210.0,
                    'relative_humidity_percent' => 68.0,
                    'weather_code' => 3,
                    'observed_at' => '2026-09-06T09:00:00Z',
                ],
            ],
        ],
    ]);
});

it('returns a null distance and speed when no route is cached', function (): void {
    ride(['origin_station_code' => '021', 'destination_station_code' => '041']);

    $result = $this->getJson('/rides')->json('results.0');

    expect($result['distance_meters'])->toBeNull()
        ->and($result['speed_kmh'])->toBeNull()
        ->and($result['expected_duration_seconds'])->toBeNull()
        ->and($result['duration_vs_expected_seconds'])->toBeNull()
        ->and($result['weather'])->toBeNull();
});

it('reports a negative delta when the ride beat the expected ride time', function (): void {
    bikeRouteBetween('021', '041', 1500.0, 400.0);
    ride([
        'origin_station_code' => '021',
        'destination_station_code' => '041',
        'checkout_time' => '2026-09-06 08:57:00',
        'checkin_time' => '2026-09-06 09:02:00',
    ]);

    $result = $this->getJson('/rides')->json('results.0');

    // 300 seconds ridden against the 400 seconds the router predicted.
    expect($result['actual_duration_seconds'])->toBe(300.0)
        ->and($result['duration_vs_expected_seconds'])->toBe(-100.0);
});

it('returns a null expected ride time when no route is cached', function (): void {
    ride(['origin_station_code' => '021', 'destination_station_code' => '999']);

    $result = $this->getJson('/rides')->json('results.0');

    expect($result['expected_duration_seconds'])->toBeNull()
        ->and($result['actual_duration_seconds'])->not->toBeNull()
        ->and($result['duration_vs_expected_seconds'])->toBeNull();
});

it('ignores routes cached for another travel mode', function (): void {
    bikeRouteBetween('021', '041', 1500.0)->update(['mode' => TravelMode::Foot]);
    ride(['origin_station_code' => '021', 'destination_station_code' => '041']);

    expect($this->getJson('/rides')->json('results.0.distance_meters'))->toBeNull();
});

it('returns a null speed when the ride has no duration', function (): void {
    bikeRouteBetween('021', '041', 1500.0);
    ride(['origin_station_code' => '021', 'destination_station_code' => '041', 'duration' => 0]);

    $result = $this->getJson('/rides')->json('results.0');

    expect($result['distance_meters'])->toBe(1500.0)
        ->and($result['speed_kmh'])->toBeNull();
});

it('rounds the speed to two decimals', function (): void {
    bikeRouteBetween('021', '041', 2345.0);
    ride(['origin_station_code' => '021', 'destination_station_code' => '041', 'duration' => 7]);

    // 2.345 km in 7 minutes is 20.1_ km/h.
    expect($this->getJson('/rides')->json('results.0.speed_kmh'))->toBe(20.1);
});
