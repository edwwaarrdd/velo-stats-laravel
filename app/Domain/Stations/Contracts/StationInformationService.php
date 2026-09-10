<?php

namespace App\Domain\Stations\Contracts;

use Illuminate\Support\Collection;

interface StationInformationService
{
    /**
     * @return Collection<string, array<string, mixed>>
     */
    public function fetchStations(): Collection;
}
