<?php

namespace App\Domain\Rides\Console\Commands;

use App\Domain\Rides\Contracts\RideDataSource;
use App\Domain\Rides\Models\Ride;
use App\Domain\Rides\Services\JsonFileRideService;
use Illuminate\Console\Command;

class LoadRides extends Command
{
    protected $signature = 'rides:load {--path= : Path to the rides JSON export (defaults to data/rides.json)}';

    protected $description = 'Load customer ride history from a JSON export into the database.';

    public function handle(RideDataSource $rides): int
    {
        $path = $this->option('path');

        if ($path !== null) {
            $rides = new JsonFileRideService($path);
        }

        $fetched = $rides->fetchRides();

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($fetched as $attributes) {
            $ride = Ride::updateOrCreate(
                ['ride_id' => $attributes['ride_id']],
                $attributes,
            );

            $ride->wasRecentlyCreated ? $createdCount++ : $updatedCount++;
        }

        $this->info("Loaded {$fetched->count()} rides ({$createdCount} created, {$updatedCount} updated).");

        return self::SUCCESS;
    }
}
