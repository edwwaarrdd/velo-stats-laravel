<?php

namespace App\Domain\Rides\Console\Commands;

use App\Domain\Rides\Contracts\RideRepository;
use App\Domain\Rides\Jobs\CheckRideDistance;
use Illuminate\Console\Command;

class CheckRideDistances extends Command
{
    protected $signature = 'rides:check-distances';

    protected $description = 'Queue a job per unchecked ride to calculate and cache the distance between its origin and destination stations.';

    public function __construct(private readonly RideRepository $rides)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $dispatchedCount = 0;

        foreach ($this->rides->withoutDistanceChecked() as $ride) {
            CheckRideDistance::dispatch($ride->ride_id);
            $dispatchedCount++;
        }

        $this->info("Dispatched {$dispatchedCount} ride distance check task(s).");

        return self::SUCCESS;
    }
}
