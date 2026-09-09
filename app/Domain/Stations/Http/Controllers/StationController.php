<?php

namespace App\Domain\Stations\Http\Controllers;

use App\Domain\Stations\Http\Resources\StationResource;
use App\Domain\Stations\Models\Station;
use App\Http\Controllers\Controller;
use App\Support\ApiJson;
use Illuminate\Http\JsonResponse;

class StationController extends Controller
{
    /**
     * List every known station with its coordinates.
     */
    public function index(): JsonResponse
    {
        return ApiJson::response([
            'results' => StationResource::collection(Station::all())->resolve(),
        ]);
    }
}
