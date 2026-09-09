<?php

namespace App\Http\Resources;

use App\Models\Ride;
use App\Support\ApiDateTime;
use App\Support\Round;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Ride
 *
 * @property float|null $distance_meters Distance of the cached bike route between the ride's stations.
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
            'weather' => $this->whenLoaded('weather', fn (): WeatherResource => WeatherResource::make($this->weather)),
        ];
    }

    /**
     * The ride's average speed in km/h, or null when the distance is unknown.
     */
    private function speedKmh(): ?float
    {
        if ($this->distance_meters === null || $this->duration === 0) {
            return null;
        }

        return Round::money(($this->distance_meters / 1000) / ($this->duration / 60));
    }
}
