<?php

namespace App\Domain\Routing\Contracts;

use App\Domain\Routing\Enums\TravelMode;
use App\ValueObjects\Coordinate;
use App\Domain\Routing\ValueObjects\Route;

interface RouteService
{
    public function getRoute(Coordinate $origin, Coordinate $destination, TravelMode $mode): Route;
}
