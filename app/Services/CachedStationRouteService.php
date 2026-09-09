<?php

namespace App\Services;

use App\Contracts\RouteService;
use App\Enums\TravelMode;
use App\Models\Station;
use App\Models\StationRoute;
use App\ValueObjects\Coordinate;
use App\ValueObjects\Route;

/**
 * Calculates routes between stations, caching results so a route between the
 * same pair of stations and travel mode is only ever calculated once.
 */
class CachedStationRouteService
{
    public function __construct(private readonly RouteService $routeService) {}

    public function getRoute(Station $origin, Station $destination, TravelMode $mode): Route
    {
        $cached = StationRoute::query()
            ->where('origin_station_id', $origin->station_id)
            ->where('destination_station_id', $destination->station_id)
            ->where('mode', $mode)
            ->first();

        if ($cached !== null) {
            return new Route($cached->distance_meters, $cached->duration_seconds);
        }

        $route = $this->routeService->getRoute(
            new Coordinate($origin->lat, $origin->lon),
            new Coordinate($destination->lat, $destination->lon),
            $mode,
        );

        StationRoute::create([
            'origin_station_id' => $origin->station_id,
            'destination_station_id' => $destination->station_id,
            'mode' => $mode,
            'distance_meters' => $route->distanceMeters,
            'duration_seconds' => $route->durationSeconds,
        ]);

        return $route;
    }
}
