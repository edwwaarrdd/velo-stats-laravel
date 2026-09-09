<?php

use App\Domain\Rides\Models\Ride;
use App\Domain\Weather\Models\WeatherRecord;
use App\Domain\Weather\Services\CachedRideWeatherService;
use App\ValueObjects\Coordinate;
use Illuminate\Support\Facades\Http;

function fakeArchive(float $temperature): void
{
    Http::fake(['archive-api.open-meteo.com/*' => Http::response([
        'hourly' => [
            'time' => ['2026-09-06T09:00'],
            'temperature_2m' => [$temperature],
            'apparent_temperature' => [17.1],
            'precipitation' => [0.2],
            'rain' => [0.2],
            'snowfall' => [0.0],
            'cloud_cover' => [42.0],
            'wind_speed_10m' => [11.2],
            'wind_gusts_10m' => [24.5],
            'wind_direction_10m' => [210.0],
            'relative_humidity_2m' => [68.0],
            'weather_code' => [3],
        ],
    ])]);
}

beforeEach(function (): void {
    $this->ride = Ride::factory()->create([
        'checkout_time' => '2026-09-06 08:57:02',
        'checkin_time' => '2026-09-06 09:05:30',
    ]);
});

it('fetches and caches the weather at the ride checkin time', function (): void {
    fakeArchive(18.0);

    $observation = app(CachedRideWeatherService::class)
        ->getWeather($this->ride, new Coordinate(51.19548, 4.41919));

    expect($observation->temperatureC)->toBe(18.0);

    $this->assertDatabaseHas('weather_records', [
        'ride_id' => $this->ride->ride_id,
        'temperature_c' => 18.0,
        'weather_code' => 3,
    ]);
});

it('returns the cached weather without calling the archive again', function (): void {
    WeatherRecord::factory()->create(['ride_id' => $this->ride->ride_id, 'temperature_c' => 5.5]);

    $observation = app(CachedRideWeatherService::class)
        ->getWeather($this->ride, new Coordinate(51.19548, 4.41919));

    expect($observation->temperatureC)->toBe(5.5);

    Http::assertNothingSent();
});

it('refetches and replaces the cached weather when forced', function (): void {
    WeatherRecord::factory()->create(['ride_id' => $this->ride->ride_id, 'temperature_c' => 5.5]);
    fakeArchive(18.0);

    $observation = app(CachedRideWeatherService::class)
        ->getWeather($this->ride, new Coordinate(51.19548, 4.41919), force: true);

    expect($observation->temperatureC)->toBe(18.0)
        ->and(WeatherRecord::where('ride_id', $this->ride->ride_id)->count())->toBe(1);

    $this->assertDatabaseHas('weather_records', [
        'ride_id' => $this->ride->ride_id,
        'temperature_c' => 18.0,
    ]);
});
