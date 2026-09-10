<?php

namespace App\Domain\Stations\Http\Controllers;

use App\Domain\Stations\Contracts\StationRepository;
use App\Domain\Stations\Http\Resources\StationResource;
use App\Support\ApiJson;
use Illuminate\Http\JsonResponse;

class ListStationsRequestHandler
{
    public function __construct(private readonly StationRepository $stations) {}

    public function __invoke(): JsonResponse
    {
        return ApiJson::response([
            'results' => StationResource::collection($this->stations->all())->resolve(),
        ]);
    }
}
