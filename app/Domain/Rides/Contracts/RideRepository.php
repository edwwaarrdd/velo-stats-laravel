<?php

namespace App\Domain\Rides\Contracts;

use App\Domain\Rides\Models\Ride;
use App\Domain\Routing\Models\StationRoute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface RideRepository
{
    public function findOrFail(int $rideId): Ride;

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function updateOrCreate(array $attributes): Ride;

    /**
     * @return Collection<int, Ride>
     */
    public function withoutDistanceChecked(): Collection;

    /**
     * @return Collection<int, Ride>
     */
    public function forWeatherCheck(bool $force): Collection;

    public function markDistanceChecked(Ride $ride): void;

    public function markWeatherChecked(Ride $ride): void;

    /**
     * @return Collection<int, Carbon>
     */
    public function checkoutTimes(): Collection;

    /**
     * @param  Builder<StationRoute>  $distanceMeters
     * @param  Builder<StationRoute>  $expectedDurationSeconds
     * @return Collection<int, Ride>
     */
    public function listWithRouteAndWeather(Builder $distanceMeters, Builder $expectedDurationSeconds): Collection;

    /**
     * @param  Builder<StationRoute>  $distanceMeters
     * @return Builder<Ride>
     */
    public function summaryQuery(Builder $distanceMeters): Builder;
}
