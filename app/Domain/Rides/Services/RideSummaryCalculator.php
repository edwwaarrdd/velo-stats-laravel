<?php

namespace App\Domain\Rides\Services;

use App\Domain\Rides\Models\Ride;
use App\Support\Round;
use Illuminate\Support\Facades\DB;

/**
 * Aggregates duration and distance statistics across every ride.
 */
class RideSummaryCalculator
{
    /**
     * @return array<string, int|float|null>
     */
    public function calculate(): array
    {
        $rides = Ride::query()
            ->select('duration')
            ->addSelect(['distance_meters' => RideRouteSubquery::distanceMeters()]);

        $stats = DB::query()
            ->fromSub($rides, 'rides')
            ->selectRaw('COUNT(*) as total_rides')
            ->selectRaw('SUM(duration) as total_duration')
            ->selectRaw('AVG(duration) as average_duration')
            ->selectRaw('MAX(duration) as longest_ride_duration')
            ->selectRaw('MIN(duration) as shortest_ride_duration')
            ->selectRaw('SUM(distance_meters) as total_distance_meters')
            ->selectRaw('AVG(distance_meters) as average_distance_meters')
            ->first();

        return [
            'total_rides' => (int) $stats->total_rides,
            'total_duration' => $this->nullableInt($stats->total_duration),
            'average_duration' => Round::money($stats->average_duration),
            'longest_ride_duration' => $this->nullableInt($stats->longest_ride_duration),
            'shortest_ride_duration' => $this->nullableInt($stats->shortest_ride_duration),
            'total_distance_meters' => $this->nullableFloat($stats->total_distance_meters),
            'average_distance_meters' => Round::money($stats->average_distance_meters),
        ];
    }

    private function nullableInt(int|float|null $value): ?int
    {
        return $value === null ? null : (int) $value;
    }

    private function nullableFloat(int|float|null $value): ?float
    {
        return $value === null ? null : (float) $value;
    }
}
