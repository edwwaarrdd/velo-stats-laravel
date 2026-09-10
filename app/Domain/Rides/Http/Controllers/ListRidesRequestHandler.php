<?php

namespace App\Domain\Rides\Http\Controllers;

use App\Domain\Rides\Contracts\RideRepository;
use App\Domain\Rides\Http\Resources\RideResource;
use App\Domain\Rides\Services\RideRouteSubquery;
use App\Support\ApiJson;
use Illuminate\Http\JsonResponse;

class ListRidesRequestHandler
{
    public function __construct(
        private readonly RideRepository $rides,
        private readonly RideRouteSubquery $routeSubquery,
    ) {}

    public function __invoke(): JsonResponse
    {
        $rides = $this->rides->listWithRouteAndWeather(
            $this->routeSubquery->distanceMeters(),
            $this->routeSubquery->expectedDurationSeconds(),
        );

        return ApiJson::response([
            'results' => RideResource::collection($rides)->resolve(),
        ]);
    }
}
