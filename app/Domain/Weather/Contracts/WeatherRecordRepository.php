<?php

namespace App\Domain\Weather\Contracts;

use App\Domain\Weather\Models\WeatherRecord;

interface WeatherRecordRepository
{
    public function findByRideId(int $rideId): ?WeatherRecord;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateOrCreate(int $rideId, array $attributes): WeatherRecord;
}
