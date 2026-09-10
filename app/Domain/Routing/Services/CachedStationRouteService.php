<?php

namespace App\Domain\Routing\Services;

use App\Domain\Routing\Contracts\RouteService;
use App\Domain\Routing\Contracts\StationRouteRepository;
use App\Domain\Routing\Enums\TravelMode;
use App\Domain\Routing\ValueObjects\Route;
use App\Domain\Stations\Models\Station;
use App\Support\Coordinate;

/**
 * Calculates routes between stations, caching results so a route between the
 * same pair of stations and travel mode is only ever calculated once.
 */
class CachedStationRouteService
{
    public function __construct(
        private readonly RouteService $routeService,
        private readonly StationRouteRepository $stationRoutes,
    ) {}

    public function getRoute(Station $origin, Station $destination, TravelMode $mode): Route
    {
        $cached = $this->stationRoutes->findCachedRoute($origin->station_id, $destination->station_id, $mode);

        if ($cached !== null) {
            return new Route($cached->distance_meters, $cached->duration_seconds);
        }

        $route = $this->routeService->getRoute(
            new Coordinate($origin->lat, $origin->lon),
            new Coordinate($destination->lat, $destination->lon),
            $mode,
        );

        $this->stationRoutes->create([
            'origin_station_id' => $origin->station_id,
            'destination_station_id' => $destination->station_id,
            'mode' => $mode,
            'distance_meters' => $route->distanceMeters,
            'duration_seconds' => $route->durationSeconds,
        ]);

        return $route;
    }
}
