<?php

use App\Services\JsonFileRideService;

function writeRidesExport(array $rides): string
{
    $path = tempnam(sys_get_temp_dir(), 'rides').'.json';
    file_put_contents($path, json_encode(['data' => ['CustomerRides' => $rides]]));

    return $path;
}

it('maps the ride export onto ride attributes', function (): void {
    $path = writeRidesExport([[
        'id' => 73147208,
        'accountId' => 123,
        'status' => 'Completed',
        'duration' => 8,
        'bikeNumber' => '5097',
        'originStationCode' => '021',
        'originStation' => '021- Driekoningen',
        'originSlotId' => '15',
        'checkoutTime' => '2026-09-06 08:57:02',
        'destinationStationCode' => '041',
        'destinationStation' => '041- Van Eyck',
        'destinationSlotId' => '23',
        'checkinTime' => '2026-09-06 09:05:30',
    ]]);

    $ride = (new JsonFileRideService($path))->fetchRides()->get(73147208);

    expect($ride['ride_id'])->toBe(73147208)
        ->and($ride['account_id'])->toBe(123)
        ->and($ride['status'])->toBe('Completed')
        ->and($ride['duration'])->toBe(8)
        ->and($ride['bike_number'])->toBe('5097')
        ->and($ride['origin_station_code'])->toBe('021')
        ->and($ride['origin_station'])->toBe('021- Driekoningen')
        ->and($ride['origin_slot_id'])->toBe('15')
        ->and($ride['checkout_time']->toDateTimeString())->toBe('2026-09-06 08:57:02')
        ->and($ride['checkout_time']->timezoneName)->toBe('UTC')
        ->and($ride['destination_station_code'])->toBe('041')
        ->and($ride['checkin_time']->toDateTimeString())->toBe('2026-09-06 09:05:30');

    unlink($path);
});

it('keys the rides by ride id', function (): void {
    $path = writeRidesExport([
        ['id' => 1, 'accountId' => 1, 'status' => 'Completed', 'duration' => 5, 'bikeNumber' => '1',
            'originStationCode' => '021', 'originStation' => 'a', 'originSlotId' => '1', 'checkoutTime' => '2026-01-01 08:00:00',
            'destinationStationCode' => '041', 'destinationStation' => 'b', 'destinationSlotId' => '2', 'checkinTime' => '2026-01-01 08:05:00'],
        ['id' => 2, 'accountId' => 1, 'status' => 'Completed', 'duration' => 5, 'bikeNumber' => '1',
            'originStationCode' => '021', 'originStation' => 'a', 'originSlotId' => '1', 'checkoutTime' => '2026-01-02 08:00:00',
            'destinationStationCode' => '041', 'destinationStation' => 'b', 'destinationSlotId' => '2', 'checkinTime' => '2026-01-02 08:05:00'],
    ]);

    expect((new JsonFileRideService($path))->fetchRides()->keys()->all())->toBe([1, 2]);

    unlink($path);
});

it('fails when the export is missing', function (): void {
    (new JsonFileRideService('/nowhere/rides.json'))->fetchRides();
})->throws(RuntimeException::class, 'Rides export not found at /nowhere/rides.json.');
