<?php

namespace App\Domain\Rides\Jobs;

use App\Domain\Rides\Contracts\RideRepository;
use App\Domain\Routing\Enums\TravelMode;
use App\Domain\Routing\Services\CachedStationRouteService;
use App\Domain\Stations\Contracts\StationRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Psr\Log\LoggerInterface;

class CheckRideDistance implements ShouldQueue
{
    use Queueable;

    /** A single worker consumes this queue, so the free routing API is never called concurrently. */
    public const QUEUE = 'ride_distance_checks';

    public function __construct(private readonly int $rideId)
    {
        $this->onQueue(self::QUEUE);
    }

    public function handle(
        CachedStationRouteService $routeService,
        LoggerInterface $logger,
        RideRepository $rides,
        StationRepository $stations,
    ): void {
        $ride = $rides->findOrFail($this->rideId);

        if ($ride->distance_checked_at !== null) {
            return;
        }

        $origin = $stations->find($ride->origin_station_code);
        $destination = $stations->find($ride->destination_station_code);

        if ($origin === null || $destination === null) {
            $logger->error("Cannot check distance for ride {$this->rideId}: unknown station code(s) {$ride->origin_station_code} / {$ride->destination_station_code}");

            return;
        }

        $routeService->getRoute($origin, $destination, TravelMode::Bike);

        $rides->markDistanceChecked($ride);
    }
}
