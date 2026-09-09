<?php

use App\Jobs\CheckRideWeather;
use App\Models\Ride;
use App\Models\Station;
use App\Models\WeatherRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

function fakeOpenMeteo(float $temperature = 18.0): void
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

function rideOn6September(array $attributes = []): Ride
{
    return Ride::factory()->create(array_replace([
        'origin_station_code' => '021',
        'checkout_time' => '2026-09-06 08:57:02',
        'checkin_time' => '2026-09-06 09:05:30',
    ], $attributes));
}

it('runs on the ride weather queue', function (): void {
    expect((new CheckRideWeather(1))->queue)->toBe('ride_weather_checks');
});

it('caches the weather and stamps the ride as checked', function (): void {
    Station::factory()->create(['station_id' => '021']);
    fakeOpenMeteo();
    $ride = rideOn6September();

    CheckRideWeather::dispatchSync($ride->ride_id);

    expect($ride->fresh()->weather_checked_at)->toBeInstanceOf(Carbon::class)
        ->and($ride->fresh()->weather->temperature_c)->toBe(18.0);
});

it('skips a ride whose weather was already checked', function (): void {
    Station::factory()->create(['station_id' => '021']);
    $ride = rideOn6September(['weather_checked_at' => '2026-01-01 00:00:00']);

    CheckRideWeather::dispatchSync($ride->ride_id);

    expect(WeatherRecord::count())->toBe(0);
    Http::assertNothingSent();
});

it('refetches the weather of an already checked ride when forced', function (): void {
    Station::factory()->create(['station_id' => '021']);
    fakeOpenMeteo(21.5);
    $ride = rideOn6September(['weather_checked_at' => '2026-01-01 00:00:00']);
    WeatherRecord::factory()->create(['ride_id' => $ride->ride_id, 'temperature_c' => 5.5]);

    CheckRideWeather::dispatchSync($ride->ride_id, true);

    expect($ride->fresh()->weather->temperature_c)->toBe(21.5);
});

it('logs and leaves the ride unchecked when the origin station is unknown', function (): void {
    $ride = rideOn6September(['origin_station_code' => '999']);

    Log::shouldReceive('error')->once()->withArgs(
        fn (string $message): bool => str_contains($message, 'unknown origin station code 999')
    );

    CheckRideWeather::dispatchSync($ride->ride_id);

    expect($ride->fresh()->weather_checked_at)->toBeNull()
        ->and(WeatherRecord::count())->toBe(0);
    Http::assertNothingSent();
});
