<?php

namespace App\Domain\Stations\Contracts;

use Illuminate\Support\Collection;

interface StationInformationService
{
    /**
     * Fetch bike-share station information, keyed by station id.
     *
     * @return Collection<string, array<string, mixed>>
     */
    public function fetchStations(): Collection;
}
