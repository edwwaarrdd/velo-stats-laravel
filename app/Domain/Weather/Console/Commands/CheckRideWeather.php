<?php

namespace App\Domain\Weather\Console\Commands;

use App\Domain\Rides\Contracts\RideRepository;
use App\Domain\Weather\Jobs\CheckRideWeather as CheckRideWeatherJob;
use Illuminate\Console\Command;

class CheckRideWeather extends Command
{
    protected $signature = 'rides:check-weather {--force : Re-fetch weather for every ride, even if already checked}';

    protected $description = 'Queue a job per ride to fetch and cache the weather at its origin station and checkin time from Open-Meteo.';

    public function __construct(private readonly RideRepository $rides)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $dispatchedCount = 0;

        foreach ($this->rides->forWeatherCheck($force) as $ride) {
            CheckRideWeatherJob::dispatch($ride->ride_id, $force);
            $dispatchedCount++;
        }

        $this->info("Dispatched {$dispatchedCount} ride weather check task(s).");

        return self::SUCCESS;
    }
}
