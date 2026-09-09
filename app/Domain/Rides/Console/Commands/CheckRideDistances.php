<?php

namespace App\Domain\Rides\Console\Commands;

use App\Domain\Rides\Jobs\CheckRideDistance;
use App\Domain\Rides\Models\Ride;
use Illuminate\Console\Command;

class CheckRideDistances extends Command
{
    protected $signature = 'rides:check-distances';

    protected $description = 'Queue a job per unchecked ride to calculate and cache the distance between its origin and destination stations.';

    public function handle(): int
    {
        $dispatchedCount = 0;

        Ride::query()
            ->whereNull('distance_checked_at')
            ->each(function (Ride $ride) use (&$dispatchedCount): void {
                CheckRideDistance::dispatch($ride->ride_id);
                $dispatchedCount++;
            });

        $this->info("Dispatched {$dispatchedCount} ride distance check task(s).");

        return self::SUCCESS;
    }
}
