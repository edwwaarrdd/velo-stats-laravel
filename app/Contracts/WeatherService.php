<?php

namespace App\Contracts;

use App\ValueObjects\Coordinate;
use App\ValueObjects\WeatherObservation;
use DateTimeInterface;

interface WeatherService
{
    /**
     * Fetch the weather observed at a location for the hour of the given time.
     */
    public function getWeather(Coordinate $location, DateTimeInterface $at): WeatherObservation;
}
