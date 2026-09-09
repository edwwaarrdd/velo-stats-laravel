<?php

use App\Models\Ride;

function ridePayload(int $id, string $checkoutTime): array
{
    return [
        'id' => $id,
        'accountId' => 123,
        'status' => 'Completed',
        'duration' => 8,
        'bikeNumber' => '5097',
        'originStationCode' => '021',
        'originStation' => '021- Driekoningen',
        'originSlotId' => '15',
        'checkoutTime' => $checkoutTime,
        'destinationStationCode' => '041',
        'destinationStation' => '041- Van Eyck',
        'destinationSlotId' => '23',
        'checkinTime' => $checkoutTime,
    ];
}

function ridesExport(array $rides): string
{
    $path = tempnam(sys_get_temp_dir(), 'rides').'.json';
    file_put_contents($path, json_encode(['data' => ['CustomerRides' => $rides]]));

    return $path;
}

it('creates the rides from the export at the given path', function (): void {
    $path = ridesExport([ridePayload(1, '2026-01-01 08:00:00'), ridePayload(2, '2026-01-02 08:00:00')]);

    $this->artisan('rides:load', ['--path' => $path])
        ->expectsOutputToContain('Loaded 2 rides (2 created, 0 updated).')
        ->assertSuccessful();

    expect(Ride::count())->toBe(2)
        ->and(Ride::find(1)->checkout_time->toDateTimeString())->toBe('2026-01-01 08:00:00');

    unlink($path);
});

it('updates the rides it already knows about', function (): void {
    Ride::factory()->create(['ride_id' => 1, 'status' => 'InProgress']);
    $path = ridesExport([ridePayload(1, '2026-01-01 08:00:00'), ridePayload(2, '2026-01-02 08:00:00')]);

    $this->artisan('rides:load', ['--path' => $path])
        ->expectsOutputToContain('Loaded 2 rides (1 created, 1 updated).')
        ->assertSuccessful();

    expect(Ride::find(1)->status)->toBe('Completed');

    unlink($path);
});

it('loads the bundled export when no path is given', function (): void {
    $this->artisan('rides:load')->assertSuccessful();

    expect(Ride::count())->toBeGreaterThan(0);
});
