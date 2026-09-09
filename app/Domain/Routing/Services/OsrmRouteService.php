<?php

namespace App\Domain\Routing\Services;

use App\Domain\Routing\Contracts\RouteService;
use App\Domain\Routing\Enums\TravelMode;
use App\Domain\Routing\ValueObjects\Route;
use App\Support\Coordinate;
use Illuminate\Http\Client\Factory as HttpClient;
use RuntimeException;

class OsrmRouteService implements RouteService
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $baseUrl,
    ) {}

    public function getRoute(Coordinate $origin, Coordinate $destination, TravelMode $mode): Route
    {
        // OSRM expects coordinates as "lon,lat", not "lat,lon".
        $coordinates = "{$origin->lon},{$origin->lat};{$destination->lon},{$destination->lat}";

        $payload = $this->http->connectTimeout(3)
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
