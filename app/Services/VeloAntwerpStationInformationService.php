<?php

namespace App\Services;

use App\Contracts\StationInformationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

/**
 * Fetches Velo Antwerp station information from the public GBFS feed.
 */
class VeloAntwerpStationInformationService implements StationInformationService
{
    public function __construct(private readonly string $url)
    {
        //
    }

    /**
     * @return Collection<string, array<string, mixed>>
     */
    public function fetchStations(): Collection
    {
        $payload = Http::connectTimeout(3)
            ->timeout(10)
            ->get($this->url)
            ->throw()
            ->json();

        return collect($payload['data']['stations'])
            ->map(fn (array $station): array => [
                'station_id' => (string) $station['station_id'],
                'name' => (string) $station['name'],
                'short_name' => (string) $station['short_name'],
                'lat' => (float) $station['lat'],
                'lon' => (float) $station['lon'],
                'address' => (string) $station['address'],
                'post_code' => (string) $station['post_code'],
                'rental_methods' => array_values($station['rental_methods'] ?? []),
                'capacity' => (int) ($station['capacity'] ?? 0),
            ])
            ->keyBy('station_id');
    }
}
