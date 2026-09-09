<?php

namespace App\Domain\Weather\ValueObjects;

use Illuminate\Support\Carbon;

readonly class WeatherObservation
{
    public function __construct(
        public float $temperatureC,
        public float $apparentTemperatureC,
        public float $precipitationMm,
        public float $rainMm,
        public float $snowfallCm,
        public float $cloudCoverPercent,
        public float $windSpeedKmh,
        public float $windGustsKmh,
        public float $windDirectionDegrees,
        public float $relativeHumidityPercent,
        public int $weatherCode,
        public Carbon $observedAt,
    ) {}

    /**
     * @param  array<string, array<int, mixed>>  $hourly
     */
    public static function fromOpenMeteoHourly(array $hourly, int $index): self
    {
        return new self(
            temperatureC: (float) $hourly['temperature_2m'][$index],
            apparentTemperatureC: (float) $hourly['apparent_temperature'][$index],
            precipitationMm: (float) $hourly['precipitation'][$index],
            rainMm: (float) $hourly['rain'][$index],
            snowfallCm: (float) $hourly['snowfall'][$index],
            cloudCoverPercent: (float) $hourly['cloud_cover'][$index],
            windSpeedKmh: (float) $hourly['wind_speed_10m'][$index],
            windGustsKmh: (float) $hourly['wind_gusts_10m'][$index],
            windDirectionDegrees: (float) $hourly['wind_direction_10m'][$index],
            relativeHumidityPercent: (float) $hourly['relative_humidity_2m'][$index],
            weatherCode: (int) $hourly['weather_code'][$index],
            observedAt: Carbon::parse((string) $hourly['time'][$index], 'UTC'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toAttributes(): array
    {
        return [
            'temperature_c' => $this->temperatureC,
            'apparent_temperature_c' => $this->apparentTemperatureC,
            'precipitation_mm' => $this->precipitationMm,
            'rain_mm' => $this->rainMm,
            'snowfall_cm' => $this->snowfallCm,
            'cloud_cover_percent' => $this->cloudCoverPercent,
            'wind_speed_kmh' => $this->windSpeedKmh,
            'wind_gusts_kmh' => $this->windGustsKmh,
            'wind_direction_degrees' => $this->windDirectionDegrees,
            'relative_humidity_percent' => $this->relativeHumidityPercent,
            'weather_code' => $this->weatherCode,
            'observed_at' => $this->observedAt,
        ];
    }
}
