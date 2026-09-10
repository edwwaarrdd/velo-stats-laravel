<?php

namespace App\Domain\Stations\Contracts;

use App\Domain\Stations\Models\Station;
use Illuminate\Support\Collection;

interface StationRepository
{
    /**
     * @return Collection<int, Station>
     */
    public function all(): Collection;

    public function find(string $stationId): ?Station;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateOrCreate(array $attributes): Station;
}
