<?php

namespace App\Domain\Rides\Http\Resources;

use App\Domain\Rides\Models\Ride;
use App\Domain\Weather\Http\Resources\WeatherResource;
use App\Support\ApiDateTime;
use App\Support\Round;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Ride
 *
 * @property float|null $distance_meters Distance of the cached bike route between the ride's stations.
 * @property float|null $expected_duration_seconds Ride time the router predicts for that cached bike route.
 */
class RideResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'ride_id' => $this->ride_id,
            'account_id' => $this->account_id,
            'status' => $this->status,
            'duration' => $this->duration,
            'bike_number' => $this->bike_number,
            'origin_station_code' => $this->origin_station_code,
            'origin_station' => $this->origin_station,
            'origin_slot_id' => $this->origin_slot_id,
            'checkout_time' => ApiDateTime::datetime($this->checkout_time),
            'destination_station_code' => $this->destination_station_code,
            'destination_station' => $this->destination_station,
            'destination_slot_id' => $this->destination_slot_id,
            'checkin_time' => ApiDateTime::datetime($this->checkin_time),
            'distance_meters' => $this->distance_meters,
            'speed_kmh' => $this->speedKmh(),
            'expected_duration_seconds' => Round::money($this->expected_duration_seconds),
            'actual_duration_seconds' => $this->actualDurationSeconds(),
            'duration_vs_expected_seconds' => $this->durationVsExpectedSeconds(),
            'weather' => $this->whenLoaded('weather', fn (): WeatherResource => WeatherResource::make($this->weather)),
        ];
    }

    /**
     * This divides by the exact ride time rather than the `duration` field,
     * which truncates to whole minutes and so overstates the speed.
     */
    private function speedKmh(): ?float
    {
        $seconds = $this->actualDurationSeconds();

        if ($this->distance_meters === null || $seconds === null || $seconds <= 0.0) {
            return null;
        }

        return Round::money(($this->distance_meters / 1000) / ($seconds / 3600));
    }

    /** `duration` is only stored in whole minutes, so this recomputes to the second. */
    private function actualDurationSeconds(): ?float
    {
        if ($this->checkin_time === null || $this->checkout_time === null) {
            return null;
        }

        return Round::money($this->checkin_time->getTimestamp() - $this->checkout_time->getTimestamp());
    }

    /** Negative means faster than the router predicted. */
    private function durationVsExpectedSeconds(): ?float
    {
        $actual = $this->actualDurationSeconds();

        if ($actual === null || $this->expected_duration_seconds === null) {
            return null;
        }

        return Round::money($actual - $this->expected_duration_seconds);
    }
}
