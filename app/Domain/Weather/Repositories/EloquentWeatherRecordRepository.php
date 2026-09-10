<?php

namespace App\Domain\Weather\Repositories;

use App\Domain\Weather\Contracts\WeatherRecordRepository;
use App\Domain\Weather\Models\WeatherRecord;

class EloquentWeatherRecordRepository implements WeatherRecordRepository
{
    public function findByRideId(int $rideId): ?WeatherRecord
    {
        return WeatherRecord::query()->where('ride_id', $rideId)->first();
    }

    public function updateOrCreate(int $rideId, array $attributes): WeatherRecord
    {
        return WeatherRecord::updateOrCreate(['ride_id' => $rideId], $attributes);
    }
}
