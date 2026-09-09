<?php

use App\Models\Station;
use Illuminate\Support\Facades\Http;

function fakeStationFeed(array $stations): void
{
    Http::fake(['gbfs.smartbike.com/*' => Http::response(['data' => ['stations' => $stations]])]);
}

function stationPayload(string $stationId, string $name): array
{
    return [
        'station_id' => $stationId,
        'name' => $name,
        'short_name' => $stationId,
        'lat' => 51.19548,
        'lon' => 4.41919,
        'address' => 'Driekoningenstraat 1',
        'post_code' => '2600',
        'rental_methods' => ['KEY'],
        'capacity' => 28,
    ];
}

it('creates the stations from the feed', function (): void {
    fakeStationFeed([stationPayload('021', '021- Driekoningen'), stationPayload('041', '041- Van Eyck')]);

    $this->artisan('stations:load')
        ->expectsOutputToContain('Loaded 2 stations (2 created, 0 updated).')
        ->assertSuccessful();

    expect(Station::count())->toBe(2)
        ->and(Station::find('021')->capacity)->toBe(28);
});

it('updates the stations it already knows about', function (): void {
    Station::factory()->create(['station_id' => '021', 'name' => 'stale name']);
    fakeStationFeed([stationPayload('021', '021- Driekoningen'), stationPayload('041', '041- Van Eyck')]);

    $this->artisan('stations:load')
        ->expectsOutputToContain('Loaded 2 stations (1 created, 1 updated).')
        ->assertSuccessful();

    expect(Station::count())->toBe(2)
        ->and(Station::find('021')->name)->toBe('021- Driekoningen');
});
