<?php

namespace App\Console\Commands;

use App\Jobs\CheckRideWeather as CheckRideWeatherJob;
use App\Models\Ride;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class CheckRideWeather extends Command
{
    protected $signature = 'rides:check-weather {--force : Re-fetch weather for every ride, even if already checked}';

    protected $description = 'Queue a job per ride to fetch and cache the weather at its origin station and checkin time from Open-Meteo.';

    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $dispatchedCount = 0;

        Ride::query()
            ->unless($force, fn (Builder $query) => $query->whereNull('weather_checked_at'))
            ->each(function (Ride $ride) use ($force, &$dispatchedCount): void {
                CheckRideWeatherJob::dispatch($ride->ride_id, $force);
                $dispatchedCount++;
            });

        $this->info("Dispatched {$dispatchedCount} ride weather check task(s).");

        return self::SUCCESS;
    }
}
