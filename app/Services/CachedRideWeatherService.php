<?php

namespace App\Services;

use App\Contracts\WeatherService;
use App\Models\Ride;
use App\Models\WeatherRecord;
use App\ValueObjects\Coordinate;
use App\ValueObjects\WeatherObservation;

/**
 * Fetches the weather for a ride's checkin time and origin station, caching
 * results so the same ride's weather is only ever fetched once unless forced.
 */
class CachedRideWeatherService
{
    public function __construct(private readonly WeatherService $weatherService) {}

    public function getWeather(Ride $ride, Coordinate $location, bool $force = false): WeatherObservation
    {
        $cached = WeatherRecord::query()->where('ride_id', $ride->ride_id)->first();

        if ($cached !== null && ! $force) {
            return new WeatherObservation(
                temperatureC: $cached->temperature_c,
                apparentTemperatureC: $cached->apparent_temperature_c,
                precipitationMm: $cached->precipitation_mm,
                rainMm: $cached->rain_mm,
                snowfallCm: $cached->snowfall_cm,
                cloudCoverPercent: $cached->cloud_cover_percent,
                windSpeedKmh: $cached->wind_speed_kmh,
                windGustsKmh: $cached->wind_gusts_kmh,
                windDirectionDegrees: $cached->wind_direction_degrees,
                relativeHumidityPercent: $cached->relative_humidity_percent,
                weatherCode: $cached->weather_code,
                observedAt: $cached->observed_at,
            );
        }

        $observation = $this->weatherService->getWeather($location, $ride->checkin_time);

        WeatherRecord::updateOrCreate(
            ['ride_id' => $ride->ride_id],
            $observation->toAttributes(),
        );

        return $observation;
    }
}
