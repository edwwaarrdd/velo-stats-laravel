<?php

use App\Domain\Routing\Enums\TravelMode;
use App\Domain\Routing\Models\StationRoute;
use App\Domain\Routing\Services\CachedStationRouteService;
use App\Domain\Stations\Models\Station;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->origin = Station::factory()->create(['station_id' => '021', 'lat' => 51.19548, 'lon' => 4.41919]);
    $this->destination = Station::factory()->create(['station_id' => '041', 'lat' => 51.21797, 'lon' => 4.40243]);
});

it('calculates and caches a route the first time it is asked for', function (): void {
    Http::fake([
        'routing.openstreetmap.de/*' => Http::response([
            'code' => 'Ok',
            'routes' => [['distance' => 1502.3, 'duration' => 361.7]],
        ]),
    ]);

    $route = app(CachedStationRouteService::class)
        ->getRoute($this->origin, $this->destination, TravelMode::Bike);

    expect($route->distanceMeters)->toBe(1502.3);

    $this->assertDatabaseHas('station_routes', [
        'origin_station_id' => '021',
        'destination_station_id' => '041',
        'mode' => 'bike',
        'distance_meters' => 1502.3,
        'duration_seconds' => 361.7,
    ]);
});

it('returns the cached route without calling the routing API again', function (): void {
    StationRoute::factory()->create([
        'origin_station_id' => '021',
        'destination_station_id' => '041',
        'mode' => TravelMode::Bike,
        'distance_meters' => 999.0,
        'duration_seconds' => 111.0,
    ]);

    $route = app(CachedStationRouteService::class)
        ->getRoute($this->origin, $this->destination, TravelMode::Bike);

    expect($route->distanceMeters)->toBe(999.0)
        ->and($route->durationSeconds)->toBe(111.0);

    Http::assertNothingSent();
});

it('caches each travel mode separately', function (): void {
    StationRoute::factory()->create([
        'origin_station_id' => '021',
        'destination_station_id' => '041',
        'mode' => TravelMode::Foot,
        'distance_meters' => 999.0,
    ]);

    Http::fake([
        'routing.openstreetmap.de/*' => Http::response([
            'code' => 'Ok',
            'routes' => [['distance' => 1502.3, 'duration' => 361.7]],
        ]),
    ]);

    $route = app(CachedStationRouteService::class)
        ->getRoute($this->origin, $this->destination, TravelMode::Bike);

    expect($route->distanceMeters)->toBe(1502.3)
        ->and(StationRoute::count())->toBe(2);
});
