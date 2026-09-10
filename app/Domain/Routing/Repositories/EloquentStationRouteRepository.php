<?php

namespace App\Domain\Routing\Repositories;

use App\Domain\Routing\Contracts\StationRouteRepository;
use App\Domain\Routing\Enums\TravelMode;
use App\Domain\Routing\Models\StationRoute;
use Illuminate\Database\Eloquent\Builder;

class EloquentStationRouteRepository implements StationRouteRepository
{
    public function findCachedRoute(string $originStationId, string $destinationStationId, TravelMode $mode): ?StationRoute
    {
        return StationRoute::query()
            ->where('origin_station_id', $originStationId)
            ->where('destination_station_id', $destinationStationId)
            ->where('mode', $mode)
            ->first();
    }

    public function create(array $attributes): StationRoute
    {
        return StationRoute::create($attributes);
    }

    public function query(): Builder
    {
        return StationRoute::query();
    }
}
