<?php

namespace App\Domain\Rides\Contracts;

use Illuminate\Support\Collection;

interface RideDataSource
{
    /**
     * Fetch ride history, keyed by ride id.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function fetchRides(): Collection;
}
