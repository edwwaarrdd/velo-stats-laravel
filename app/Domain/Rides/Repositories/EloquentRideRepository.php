<?php

namespace App\Domain\Rides\Repositories;

use App\Domain\Rides\Contracts\RideRepository;
use App\Domain\Rides\Models\Ride;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentRideRepository implements RideRepository
{
    public function findOrFail(int $rideId): Ride
    {
        return Ride::query()->findOrFail($rideId);
    }

    public function updateOrCreate(array $attributes): Ride
    {
        return Ride::updateOrCreate(
            ['ride_id' => $attributes['ride_id']],
            $attributes,
        );
    }

    public function withoutDistanceChecked(): Collection
    {
        return Ride::query()->whereNull('distance_checked_at')->get();
    }

    public function forWeatherCheck(bool $force): Collection
    {
        return Ride::query()
            ->unless($force, fn (Builder $query) => $query->whereNull('weather_checked_at'))
            ->get();
    }

    public function markDistanceChecked(Ride $ride): void
    {
        $ride->update(['distance_checked_at' => now()]);
    }

    public function markWeatherChecked(Ride $ride): void
    {
        $ride->update(['weather_checked_at' => now()]);
    }

    public function checkoutTimes(): Collection
    {
        return Ride::query()->pluck('checkout_time');
    }

    public function listWithRouteAndWeather(Builder $distanceMeters, Builder $expectedDurationSeconds): Collection
    {
        return Ride::query()
            ->with('weather')
            ->select('rides.*')
            ->addSelect(['distance_meters' => $distanceMeters])
            ->addSelect(['expected_duration_seconds' => $expectedDurationSeconds])
            ->orderByDesc('checkout_time')
            ->orderByDesc('ride_id')
            ->get();
    }

    public function summaryQuery(Builder $distanceMeters): Builder
    {
        return Ride::query()
            ->select('duration')
            ->addSelect(['distance_meters' => $distanceMeters]);
    }
}
