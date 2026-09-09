<?php

namespace App\Services;

use App\Contracts\RouteService;
use App\Enums\TravelMode;
use App\ValueObjects\Coordinate;
use App\ValueObjects\Route;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OsrmRouteService implements RouteService
{
    public function __construct(private readonly string $baseUrl) {}

    public function getRoute(Coordinate $origin, Coordinate $destination, TravelMode $mode): Route
    {
        // OSRM expects coordinates as "lon,lat", not "lat,lon".
        $coordinates = "{$origin->lon},{$origin->lat};{$destination->lon},{$destination->lat}";

        $payload = Http::connectTimeout(3)
            ->timeout(10)
            ->get("{$this->baseUrl}/{$mode->osrmInstancePath()}/route/v1/{$mode->value}/{$coordinates}", ['overview' => 'false'])
            ->throw()
            ->json();

        if (($payload['code'] ?? null) !== 'Ok') {
            throw new RuntimeException('OSRM request failed: '.($payload['message'] ?? $payload['code'] ?? 'unknown error'));
        }

        return Route::fromOsrmRoute($payload['routes'][0]);
    }
}
