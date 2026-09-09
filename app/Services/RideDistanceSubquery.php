<?php

namespace App\Services;

use App\Enums\TravelMode;
use App\Models\StationRoute;
use Illuminate\Database\Eloquent\Builder;

/**
 * Builds the correlated subquery that resolves a ride's cycling distance from
 * the cached route between its origin and destination stations.
 */
class RideDistanceSubquery
{
    /**
     * @return Builder<StationRoute>
     */
    public static function make(): Builder
    {
        return StationRoute::query()
            ->select('distance_meters')
            ->whereColumn('origin_station_id', 'rides.origin_station_code')
            ->whereColumn('destination_station_id', 'rides.destination_station_code')
            ->where('mode', TravelMode::Bike)
            ->limit(1);
    }
}
