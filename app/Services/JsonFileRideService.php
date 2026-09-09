<?php

namespace App\Services;

use App\Contracts\RideDataSource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Fetches ride history from a local JSON export of the customer rides.
 */
class JsonFileRideService implements RideDataSource
{
    /**
     * The format the ride export uses for its checkout and checkin times.
     */
    private const RIDE_DATETIME_FORMAT = 'Y-m-d H:i:s';

    public function __construct(private readonly string $path) {}

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function fetchRides(): Collection
    {
        if (! is_file($this->path)) {
            throw new RuntimeException("Rides export not found at {$this->path}.");
        }

        $payload = json_decode((string) file_get_contents($this->path), associative: true, flags: JSON_THROW_ON_ERROR);

        return collect($payload['data']['CustomerRides'])
            ->map(fn (array $ride): array => [
                'ride_id' => (int) $ride['id'],
                'account_id' => (int) $ride['accountId'],
                'status' => (string) $ride['status'],
                'duration' => (int) $ride['duration'],
                'bike_number' => (string) $ride['bikeNumber'],
                'origin_station_code' => (string) $ride['originStationCode'],
                'origin_station' => (string) $ride['originStation'],
                'origin_slot_id' => (string) $ride['originSlotId'],
                'checkout_time' => $this->parseDateTime($ride['checkoutTime']),
                'destination_station_code' => (string) $ride['destinationStationCode'],
                'destination_station' => (string) $ride['destinationStation'],
                'destination_slot_id' => (string) $ride['destinationSlotId'],
                'checkin_time' => $this->parseDateTime($ride['checkinTime']),
            ])
            ->keyBy('ride_id');
    }

    private function parseDateTime(string $value): Carbon
    {
        return Carbon::createFromFormat(self::RIDE_DATETIME_FORMAT, $value, 'UTC');
    }
}
