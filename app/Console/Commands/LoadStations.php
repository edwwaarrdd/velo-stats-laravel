<?php

namespace App\Console\Commands;

use App\Contracts\StationInformationService;
use App\Models\Station;
use Illuminate\Console\Command;

class LoadStations extends Command
{
    protected $signature = 'stations:load';

    protected $description = 'Fetch Velo Antwerp station information and save it to the database.';

    public function handle(StationInformationService $stations): int
    {
        $fetched = $stations->fetchStations();

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($fetched as $attributes) {
            $station = Station::updateOrCreate(
                ['station_id' => $attributes['station_id']],
                $attributes,
            );

            $station->wasRecentlyCreated ? $createdCount++ : $updatedCount++;
        }

        $this->info("Loaded {$fetched->count()} stations ({$createdCount} created, {$updatedCount} updated).");

        return self::SUCCESS;
    }
}
