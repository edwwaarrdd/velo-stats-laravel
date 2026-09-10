<?php

namespace App\Domain\Weather\Contracts;

use App\Domain\Weather\ValueObjects\WeatherObservation;
use App\Support\Coordinate;
use DateTimeInterface;

interface WeatherService
{
    public function getWeather(Coordinate $location, DateTimeInterface $at): WeatherObservation;
}
