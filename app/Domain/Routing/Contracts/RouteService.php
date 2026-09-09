<?php

namespace App\Domain\Routing\Contracts;

use App\Domain\Routing\Enums\TravelMode;
use App\Domain\Routing\ValueObjects\Route;
use App\Support\Coordinate;

interface RouteService
{
    public function getRoute(Coordinate $origin, Coordinate $destination, TravelMode $mode): Route;
}
