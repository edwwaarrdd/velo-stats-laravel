<?php

namespace App\Domain\Stations\Services;

use App\Domain\Stations\Contracts\StationInformationService;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Support\Collection;

class VeloAntwerpStationInformationService implements StationInformationService
{
    public function __construct(
        private readonly HttpClient $http,
        private readonly string $url,
    ) {}

    /**
     * @return Collection<string, array<string, mixed>>
     */
    public function fetchStations(): Collection
    {
        $payload = $this->http->connectTimeout(3)
            ->timeout(10)
            ->get($this->url)
            ->throw()
            ->json();

        return collect($payload['data']['stations'])
            ->map($this->toAttributes(...))
            ->keyBy(fn (array $station): string => (string) $station['station_id']);
    }

    /**
     * @param  array<string, mixed>  $station
     * @return array<string, mixed>
     */
    private function toAttributes(array $station): array
    {
        return [
            'station_id' => (string) $station['station_id'],
            'name' => (string) $station['name'],
            'short_name' => (string) $station['short_name'],
            'lat' => (float) $station['lat'],
            'lon' => (float) $station['lon'],
            'address' => (string) $station['address'],
            'post_code' => (string) $station['post_code'],
            'rental_methods' => array_values((array) ($station['rental_methods'] ?? [])),
            'capacity' => (int) ($station['capacity'] ?? 0),
        ];
    }
}
