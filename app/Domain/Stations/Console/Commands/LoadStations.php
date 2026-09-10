<?php

namespace App\Domain\Stations\Console\Commands;

use App\Domain\Stations\Contracts\StationInformationService;
use App\Domain\Stations\Contracts\StationRepository;
use Illuminate\Console\Command;

class LoadStations extends Command
{
    protected $signature = 'stations:load';

    protected $description = 'Fetch Velo Antwerp station information and save it to the database.';

    public function __construct(
        private readonly StationInformationService $stations,
        private readonly StationRepository $stationRepository,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $fetched = $this->stations->fetchStations();

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($fetched as $attributes) {
            $station = $this->stationRepository->updateOrCreate($attributes);

            $station->wasRecentlyCreated ? $createdCount++ : $updatedCount++;
        }

        $this->info("Loaded {$fetched->count()} stations ({$createdCount} created, {$updatedCount} updated).");

        return self::SUCCESS;
    }
}
