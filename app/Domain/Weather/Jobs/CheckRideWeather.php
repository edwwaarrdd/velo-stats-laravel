<?php

namespace App\Domain\Weather\Jobs;

use App\Domain\Rides\Contracts\RideRepository;
use App\Domain\Stations\Contracts\StationRepository;
use App\Domain\Weather\Services\CachedRideWeatherService;
use App\Support\Coordinate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Psr\Log\LoggerInterface;

class CheckRideWeather implements ShouldQueue
{
    use Queueable;

    /** A single worker consumes this queue, so the free Open-Meteo API is never called concurrently. */
    public const QUEUE = 'ride_weather_checks';

    public function __construct(
        private readonly int $rideId,
        private readonly bool $force = false,
    ) {
        $this->onQueue(self::QUEUE);
    }

    public function handle(
        CachedRideWeatherService $weatherService,
        LoggerInterface $logger,
        RideRepository $rides,
        StationRepository $stations,
    ): void {
        $ride = $rides->findOrFail($this->rideId);

        if ($ride->weather_checked_at !== null && ! $this->force) {
            return;
        }

        $origin = $stations->find($ride->origin_station_code);

        if ($origin === null) {
            $logger->error("Cannot check weather for ride {$this->rideId}: unknown origin station code {$ride->origin_station_code}");

            return;
        }

        $weatherService->getWeather($ride, new Coordinate($origin->lat, $origin->lon), $this->force);

        $rides->markWeatherChecked($ride);
    }
}
