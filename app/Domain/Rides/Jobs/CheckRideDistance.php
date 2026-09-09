<?php

namespace App\Domain\Rides\Jobs;

use App\Domain\Rides\Models\Ride;
use App\Domain\Routing\Enums\TravelMode;
use App\Domain\Routing\Services\CachedStationRouteService;
use App\Domain\Stations\Models\Station;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Psr\Log\LoggerInterface;

/**
 * Calculates and caches the cycling distance between a ride's origin and
 * destination stations.
 */
class CheckRideDistance implements ShouldQueue
{
    use Queueable;

    /**
     * The queue this job runs on. A single worker consumes it so the free
     * routing API is never called concurrently.
     */
    public const QUEUE = 'ride_distance_checks';

    public function __construct(private readonly int $rideId)
    {
        $this->onQueue(self::QUEUE);
    }

    public function handle(CachedStationRouteService $routeService, LoggerInterface $logger): void
    {
        $ride = Ride::query()->findOrFail($this->rideId);

        if ($ride->distance_checked_at !== null) {
            return;
        }

        $origin = Station::query()->find($ride->origin_station_code);
        $destination = Station::query()->find($ride->destination_station_code);

        if ($origin === null || $destination === null) {
            $logger->error("Cannot check distance for ride {$this->rideId}: unknown station code(s) {$ride->origin_station_code} / {$ride->destination_station_code}");

            return;
        }

        $routeService->getRoute($origin, $destination, TravelMode::Bike);

        $ride->update(['distance_checked_at' => now()]);
    }
}
