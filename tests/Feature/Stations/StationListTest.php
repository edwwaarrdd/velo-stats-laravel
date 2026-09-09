<?php

use App\Domain\Stations\Models\Station;

it('returns an empty result list when there are no stations', function (): void {
    $this->getJson('/stations')->assertOk()->assertExactJson(['results' => []]);
});

it('returns every station with its coordinates', function (): void {
    Station::factory()->create([
        'station_id' => '041',
        'name' => '041- Van Eyck',
        'lat' => 51.2189,
        'lon' => 4.4131,
    ]);

    $response = $this->getJson('/stations');

    $response->assertOk()->assertExactJson([
        'results' => [
            [
                'station_id' => '041',
                'name' => '041- Van Eyck',
                'lat' => 51.2189,
                'lon' => 4.4131,
            ],
        ],
    ]);
});
