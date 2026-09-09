<?php

namespace App\Domain\Weather\Jobs;

use App\Domain\Rides\Models\Ride;
use App\Domain\Stations\Models\Station;
use App\Domain\Weather\Services\CachedRideWeatherService;
use App\ValueObjects\Coordinate;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Fetches and caches the weather at a ride's origin station and checkin time.
 */
class CheckRideWeather implements ShouldQueue
{
    use Queueable;

    /**
     * The queue this job runs on. A single worker consumes it so the free
     * Open-Meteo API is never called concurrently.
     */
    public const QUEUE = 'ride_weather_checks';

    public function __construct(
        private readonly int $rideId,
        private readonly bool $force = false,
    ) {
        $this->onQueue(self::QUEUE);
    }

    public function handle(CachedRideWeatherService $weatherService): void
    {
        $ride = Ride::query()->findOrFail($this->rideId);

        if ($ride->weather_checked_at !== null && ! $this->force) {
            return;
        }

        $origin = Station::query()->find($ride->origin_station_code);

        if ($origin === null) {
            Log::error("Cannot check weather for ride {$this->rideId}: unknown origin station code {$ride->origin_station_code}");

            return;
        }

        $weatherService->getWeather($ride, new Coordinate($origin->lat, $origin->lon), $this->force);

        $ride->update(['weather_checked_at' => now()]);
    }
}
