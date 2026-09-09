<?php

use App\Contracts\RouteService;
use App\Enums\TravelMode;
use App\ValueObjects\Coordinate;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('returns the distance and duration of the first route', function (): void {
    Http::fake([
        'router.project-osrm.org/*' => Http::response([
            'code' => 'Ok',
            'routes' => [['distance' => 1502.3, 'duration' => 361.7]],
        ]),
    ]);

    $route = app(RouteService::class)->getRoute(
        new Coordinate(51.19548, 4.41919),
        new Coordinate(51.21797, 4.40243),
        TravelMode::Bike,
    );

    expect($route->distanceMeters)->toBe(1502.3)
        ->and($route->durationSeconds)->toBe(361.7);
});

it('asks OSRM for the coordinates in lon,lat order', function (): void {
    Http::fake([
        'router.project-osrm.org/*' => Http::response([
            'code' => 'Ok',
            'routes' => [['distance' => 1.0, 'duration' => 1.0]],
        ]),
    ]);

    app(RouteService::class)->getRoute(
        new Coordinate(51.19548, 4.41919),
        new Coordinate(51.21797, 4.40243),
        TravelMode::Bike,
    );

    Http::assertSent(fn (Request $request): bool => str_contains(
        $request->url(),
        '/route/v1/bike/4.41919,51.19548;4.40243,51.21797',
    ));
});

it('fails when OSRM cannot route between the coordinates', function (): void {
    Http::fake([
        'router.project-osrm.org/*' => Http::response(['code' => 'NoRoute', 'message' => 'no route found']),
    ]);

    app(RouteService::class)->getRoute(new Coordinate(0.0, 0.0), new Coordinate(1.0, 1.0), TravelMode::Bike);
})->throws(RuntimeException::class, 'OSRM request failed: no route found');
