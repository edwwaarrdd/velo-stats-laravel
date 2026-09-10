<?php

namespace App\Domain\Routing\Contracts;

use App\Domain\Routing\Enums\TravelMode;
use App\Domain\Routing\Models\StationRoute;
use Illuminate\Database\Eloquent\Builder;

interface StationRouteRepository
{
    public function findCachedRoute(string $originStationId, string $destinationStationId, TravelMode $mode): ?StationRoute;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): StationRoute;

    /**
     * @return Builder<StationRoute>
     */
    public function query(): Builder;
}
