<?php

use App\Contracts\StationInformationService;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

it('maps the GBFS station feed onto station attributes', function (): void {
    Http::fake([
        'gbfs.smartbike.com/*' => Http::response([
            'data' => [
                'stations' => [
                    [
                        'station_id' => '021',
                        'name' => '021- Driekoningen',
                        'short_name' => '021',
                        'lat' => 51.19548,
                        'lon' => 4.41919,
                        'address' => 'Driekoningenstraat 1',
                        'post_code' => '2600',
                        'rental_methods' => ['KEY'],
                        'capacity' => 28,
                    ],
                ],
            ],
        ]),
    ]);

    $stations = app(StationInformationService::class)->fetchStations();

    expect($stations)->toHaveCount(1)
        ->and($stations->get('021'))->toBe([
            'station_id' => '021',
            'name' => '021- Driekoningen',
            'short_name' => '021',
            'lat' => 51.19548,
            'lon' => 4.41919,
            'address' => 'Driekoningenstraat 1',
            'post_code' => '2600',
            'rental_methods' => ['KEY'],
            'capacity' => 28,
        ]);
});

it('defaults the rental methods and capacity when the feed omits them', function (): void {
    Http::fake([
        'gbfs.smartbike.com/*' => Http::response([
            'data' => [
                'stations' => [[
                    'station_id' => '021',
                    'name' => '021- Driekoningen',
                    'short_name' => '021',
                    'lat' => 51.19548,
                    'lon' => 4.41919,
                    'address' => 'Driekoningenstraat 1',
                    'post_code' => '2600',
                ]],
            ],
        ]),
    ]);

    $station = app(StationInformationService::class)->fetchStations()->get('021');

    expect($station['rental_methods'])->toBe([])
        ->and($station['capacity'])->toBe(0);
});

it('fails when the GBFS feed returns an error', function (): void {
    Http::fake(['gbfs.smartbike.com/*' => Http::response(status: 503)]);

    app(StationInformationService::class)->fetchStations();
})->throws(RequestException::class);
