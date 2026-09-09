<?php

namespace App\Services;

use App\Domain\Routing\Enums\TravelMode;
use App\Domain\Routing\Models\StationRoute;
use Illuminate\Database\Eloquent\Builder;

/**
 * Builds the correlated subqueries that resolve a ride's cycling distance and
 * expected ride time from the cached route between its origin and destination
 * stations.
 */
class RideRouteSubquery
{
    /**
     * @return Builder<StationRoute>
     */
    public static function distanceMeters(): Builder
    {
        return self::cachedBikeRoute()->select('distance_meters');
    }

    /**
     * The ride time the router predicts for the route, in seconds.
     *
     * @return Builder<StationRoute>
     */
    public static function expectedDurationSeconds(): Builder
    {
        return self::cachedBikeRoute()->select('duration_seconds');
    }

    /**
     * @return Builder<StationRoute>
     */
    private static function cachedBikeRoute(): Builder
    {
        return StationRoute::query()
            ->whereColumn('origin_station_id', 'rides.origin_station_code')
            ->whereColumn('destination_station_id', 'rides.destination_station_code')
            ->where('mode', TravelMode::Bike)
            ->limit(1);
    }
}
