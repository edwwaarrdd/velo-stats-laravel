<?php

namespace App\Domain\Rides\Contracts;

use Illuminate\Support\Collection;

interface RideDataSource
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function fetchRides(): Collection;
}
