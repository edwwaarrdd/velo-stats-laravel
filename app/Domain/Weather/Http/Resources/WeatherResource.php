<?php

namespace App\Domain\Weather\Http\Resources;

use App\Domain\Weather\Models\WeatherRecord;
use App\Support\ApiDateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WeatherRecord
 */
class WeatherResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'temperature_c' => $this->temperature_c,
            'apparent_temperature_c' => $this->apparent_temperature_c,
            'precipitation_mm' => $this->precipitation_mm,
            'rain_mm' => $this->rain_mm,
            'snowfall_cm' => $this->snowfall_cm,
            'cloud_cover_percent' => $this->cloud_cover_percent,
            'wind_speed_kmh' => $this->wind_speed_kmh,
            'wind_gusts_kmh' => $this->wind_gusts_kmh,
            'wind_direction_degrees' => $this->wind_direction_degrees,
            'relative_humidity_percent' => $this->relative_humidity_percent,
            'weather_code' => $this->weather_code,
            'observed_at' => ApiDateTime::datetime($this->observed_at),
        ];
    }
}
