<?php

namespace App\Domain\Stations\Repositories;

use App\Domain\Stations\Contracts\StationRepository;
use App\Domain\Stations\Models\Station;
use Illuminate\Support\Collection;

class EloquentStationRepository implements StationRepository
{
    public function all(): Collection
    {
        return Station::all();
    }

    public function find(string $stationId): ?Station
    {
        return Station::query()->find($stationId);
    }

    public function updateOrCreate(array $attributes): Station
    {
        return Station::updateOrCreate(
            ['station_id' => $attributes['station_id']],
            $attributes,
        );
    }
}
