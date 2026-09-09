<?php

namespace App\Contracts;

use App\Enums\TravelMode;
use App\ValueObjects\Coordinate;
use App\ValueObjects\Route;

interface RouteService
{
    public function getRoute(Coordinate $origin, Coordinate $destination, TravelMode $mode): Route;
}
