<?php

namespace App\Domain\Stations\Console\Commands;

use App\Domain\Stations\Contracts\StationInformationService;
use App\Domain\Stations\Models\Station;
use Illuminate\Console\Command;

class LoadStations extends Command
{
    protected $signature = 'stations:load';

    protected $description = 'Fetch Velo Antwerp station information and save it to the database.';

    public function __construct(private readonly StationInformationService $stations)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $fetched = $this->stations->fetchStations();

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
