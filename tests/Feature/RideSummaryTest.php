<?php

use App\Domain\Routing\Enums\TravelMode;
use App\Domain\Routing\Models\StationRoute;
use App\Domain\Stations\Models\Station;
use App\Models\Ride;

function cachedBikeRoute(string $originCode, string $destinationCode, float $distanceMeters): void
{
    Station::firstOrCreate(['station_id' => $originCode], Station::factory()->make(['station_id' => $originCode])->getAttributes());
    Station::firstOrCreate(['station_id' => $destinationCode], Station::factory()->make(['station_id' => $destinationCode])->getAttributes());

    StationRoute::factory()->create([
        'origin_station_id' => $originCode,
        'destination_station_id' => $destinationCode,
        'mode' => TravelMode::Bike,
        'distance_meters' => $distanceMeters,
    ]);
}

it('reports no statistics when there are no rides', function (): void {
    $this->getJson('/rides/summary')->assertOk()->assertExactJson([
        'total_rides' => 0,
        'total_duration' => null,
        'average_duration' => null,
        'longest_ride_duration' => null,
        'shortest_ride_duration' => null,
        'total_distance_meters' => null,
        'average_distance_meters' => null,
    ]);
});

it('aggregates duration and distance across every ride', function (): void {
    cachedBikeRoute('021', '041', 1500.0);
    cachedBikeRoute('021', '076', 2500.0);

    Ride::factory()->create(['duration' => 10, 'origin_station_code' => '021', 'destination_station_code' => '041']);
    Ride::factory()->create(['duration' => 20, 'origin_station_code' => '021', 'destination_station_code' => '076']);
    Ride::factory()->create(['duration' => 15, 'origin_station_code' => '021', 'destination_station_code' => '041']);

    $this->getJson('/rides/summary')->assertOk()->assertExactJson([
        'total_rides' => 3,
        'total_duration' => 45,
        'average_duration' => 15.0,
        'longest_ride_duration' => 20,
        'shortest_ride_duration' => 10,
        'total_distance_meters' => 5500.0,
        'average_distance_meters' => 1833.33,
    ]);
});

it('averages distance over only the rides that have a cached route', function (): void {
    cachedBikeRoute('021', '041', 1500.0);

    Ride::factory()->create(['duration' => 10, 'origin_station_code' => '021', 'destination_station_code' => '041']);
    Ride::factory()->create(['duration' => 10, 'origin_station_code' => '021', 'destination_station_code' => '999']);

    $summary = $this->getJson('/rides/summary')->json();

    expect($summary['total_rides'])->toBe(2)
        ->and($summary['total_distance_meters'])->toBe(1500.0)
        ->and($summary['average_distance_meters'])->toBe(1500.0);
});

it('reports null distances when no route is cached at all', function (): void {
    Ride::factory()->create(['duration' => 10]);

    $summary = $this->getJson('/rides/summary')->json();

    expect($summary['total_distance_meters'])->toBeNull()
        ->and($summary['average_distance_meters'])->toBeNull()
        ->and($summary['total_duration'])->toBe(10);
});

it('rounds the average duration to two decimals', function (): void {
    Ride::factory()->create(['duration' => 10]);
    Ride::factory()->create(['duration' => 11]);
    Ride::factory()->create(['duration' => 11]);

    expect($this->getJson('/rides/summary')->json('average_duration'))->toBe(10.67);
});
