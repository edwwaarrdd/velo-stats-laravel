<?php

namespace App\Domain\Weather\Contracts;

use App\Domain\Weather\ValueObjects\WeatherObservation;
use App\Support\Coordinate;
use DateTimeInterface;

interface WeatherService
{
    /**
     * Fetch the weather observed at a location for the hour of the given time.
     */
    public function getWeather(Coordinate $location, DateTimeInterface $at): WeatherObservation;
}
