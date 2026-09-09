<?php

namespace App\Services;

use App\Contracts\WeatherService;
use App\ValueObjects\Coordinate;
use App\ValueObjects\WeatherObservation;
use DateTimeInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Fetches historical weather using the free Open-Meteo archive API.
 */
class OpenMeteoWeatherService implements WeatherService
{
    /**
     * The biking-relevant hourly variables requested from the archive.
     *
     * @var list<string>
     */
    public const HOURLY_VARIABLES = [
        'temperature_2m',
        'apparent_temperature',
        'precipitation',
        'rain',
        'snowfall',
        'cloud_cover',
        'wind_speed_10m',
        'wind_gusts_10m',
        'wind_direction_10m',
        'relative_humidity_2m',
        'weather_code',
    ];

    public function __construct(private readonly string $archiveUrl)
    {
        //
    }

    public function getWeather(Coordinate $location, DateTimeInterface $at): WeatherObservation
    {
        $at = Carbon::instance($at)->utc();
        $date = $at->toDateString();

        $payload = Http::connectTimeout(3)
            ->timeout(10)
            ->get($this->archiveUrl, [
                'latitude' => $location->lat,
                'longitude' => $location->lon,
                'start_date' => $date,
                'end_date' => $date,
                'hourly' => implode(',', self::HOURLY_VARIABLES),
                'timezone' => 'UTC',
            ])
            ->throw()
            ->json();

        if (! isset($payload['hourly'])) {
            throw new RuntimeException('Open-Meteo request failed: '.($payload['reason'] ?? json_encode($payload)));
        }

        $hourly = $payload['hourly'];
        $targetHour = $at->copy()->startOfHour()->format('Y-m-d\TH:00');
        $index = array_search($targetHour, $hourly['time'], strict: true);

        if ($index === false) {
            throw new RuntimeException("Open-Meteo response has no observation for {$targetHour}.");
        }

        return WeatherObservation::fromOpenMeteoHourly($hourly, $index);
    }
}
