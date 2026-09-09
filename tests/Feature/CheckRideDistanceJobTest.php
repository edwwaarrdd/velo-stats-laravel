<?php

use App\Domain\Rides\Jobs\CheckRideDistance;
use App\Domain\Rides\Models\Ride;
use App\Domain\Routing\Models\StationRoute;
use App\Domain\Stations\Models\Station;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

function fakeOsrm(float $distanceMeters = 1502.3): void
{
    Http::fake(['routing.openstreetmap.de/*' => Http::response([
        'code' => 'Ok',
        'routes' => [['distance' => $distanceMeters, 'duration' => 361.7]],
    ])]);
}

function knownStations(): void
{
    Station::factory()->create(['station_id' => '021']);
    Station::factory()->create(['station_id' => '041']);
}

it('runs on the ride distance queue', function (): void {
    expect((new CheckRideDistance(1))->queue)->toBe('ride_distance_checks');
});

it('caches the bike route and stamps the ride as checked', function (): void {
    knownStations();
    fakeOsrm();
    $ride = Ride::factory()->create(['origin_station_code' => '021', 'destination_station_code' => '041']);

    CheckRideDistance::dispatchSync($ride->ride_id);

    expect($ride->fresh()->distance_checked_at)->toBeInstanceOf(Carbon::class)
        ->and(StationRoute::where('mode', 'bike')->value('distance_meters'))->toBe(1502.3);
});

it('skips a ride whose distance was already checked', function (): void {
    knownStations();
    $ride = Ride::factory()->create([
        'origin_station_code' => '021',
        'destination_station_code' => '041',
        'distance_checked_at' => '2026-01-01 00:00:00',
    ]);

    CheckRideDistance::dispatchSync($ride->ride_id);

    expect(StationRoute::count())->toBe(0);
    Http::assertNothingSent();
});

it('logs and leaves the ride unchecked when a station code is unknown', function (): void {
    Station::factory()->create(['station_id' => '021']);
    $ride = Ride::factory()->create(['origin_station_code' => '021', 'destination_station_code' => '999']);

    Log::shouldReceive('error')->once()->withArgs(
        fn (string $message): bool => str_contains($message, 'unknown station code(s) 021 / 999')
    );

    CheckRideDistance::dispatchSync($ride->ride_id);

    expect($ride->fresh()->distance_checked_at)->toBeNull()
        ->and(StationRoute::count())->toBe(0);
    Http::assertNothingSent();
});
