<?php

use App\Contracts\WeatherService;
use App\ValueObjects\Coordinate;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

function archiveResponse(array $overrides = []): array
{
    return array_replace([
        'hourly' => [
            'time' => ['2026-09-06T08:00', '2026-09-06T09:00'],
            'temperature_2m' => [17.2, 18.0],
            'apparent_temperature' => [16.4, 17.1],
            'precipitation' => [0.0, 0.2],
            'rain' => [0.0, 0.2],
            'snowfall' => [0.0, 0.0],
            'cloud_cover' => [30.0, 42.0],
            'wind_speed_10m' => [9.8, 11.2],
            'wind_gusts_10m' => [20.1, 24.5],
            'wind_direction_10m' => [200.0, 210.0],
            'relative_humidity_2m' => [72.0, 68.0],
            'weather_code' => [1, 3],
        ],
    ], $overrides);
}

it('returns the observation for the hour of the given time', function (): void {
    Http::fake(['archive-api.open-meteo.com/*' => Http::response(archiveResponse())]);

    $observation = app(WeatherService::class)->getWeather(
        new Coordinate(51.19548, 4.41919),
        Carbon::parse('2026-09-06 09:05:30', 'UTC'),
    );

    expect($observation->temperatureC)->toBe(18.0)
        ->and($observation->apparentTemperatureC)->toBe(17.1)
        ->and($observation->precipitationMm)->toBe(0.2)
        ->and($observation->rainMm)->toBe(0.2)
        ->and($observation->snowfallCm)->toBe(0.0)
        ->and($observation->cloudCoverPercent)->toBe(42.0)
        ->and($observation->windSpeedKmh)->toBe(11.2)
        ->and($observation->windGustsKmh)->toBe(24.5)
        ->and($observation->windDirectionDegrees)->toBe(210.0)
        ->and($observation->relativeHumidityPercent)->toBe(68.0)
        ->and($observation->weatherCode)->toBe(3)
        ->and($observation->observedAt->toDateTimeString())->toBe('2026-09-06 09:00:00');
});

it('asks the archive for the observation date in UTC', function (): void {
    Http::fake(['archive-api.open-meteo.com/*' => Http::response(archiveResponse())]);

    app(WeatherService::class)->getWeather(
        new Coordinate(51.19548, 4.41919),
        Carbon::parse('2026-09-06 09:05:30', 'UTC'),
    );

    Http::assertSent(function (Request $request): bool {
        return $request['start_date'] === '2026-09-06'
            && $request['end_date'] === '2026-09-06'
            && $request['timezone'] === 'UTC'
            && str_contains((string) $request['hourly'], 'apparent_temperature');
    });
});

it('fails when the archive returns no hourly data', function (): void {
    Http::fake(['archive-api.open-meteo.com/*' => Http::response(['reason' => 'out of range'])]);

    app(WeatherService::class)->getWeather(new Coordinate(0.0, 0.0), Carbon::parse('2026-09-06 09:05:30', 'UTC'));
})->throws(RuntimeException::class, 'Open-Meteo request failed: out of range');

it('fails when the archive has no observation for the hour', function (): void {
    Http::fake(['archive-api.open-meteo.com/*' => Http::response(archiveResponse())]);

    app(WeatherService::class)->getWeather(new Coordinate(0.0, 0.0), Carbon::parse('2026-09-06 23:05:30', 'UTC'));
})->throws(RuntimeException::class, 'Open-Meteo response has no observation for 2026-09-06T23:00.');
