<?php

namespace App\Contracts;

use App\Enums\TravelMode;
use App\ValueObjects\Coordinate;
use App\ValueObjects\Route;

interface RouteService
{
    /**
     * Calculate the route between two coordinates for the given travel mode.
     */
    public function getRoute(Coordinate $origin, Coordinate $destination, TravelMode $mode): Route;
}
