<?php

namespace App\Http\Controllers;

use App\Http\Resources\RideResource;
use App\Models\Ride;
use App\Services\RideCostCalculator;
use App\Services\RideRouteSubquery;
use App\Services\RideSummaryCalculator;
use App\Support\ApiJson;
use Illuminate\Http\JsonResponse;

class RideController extends Controller
{
    /**
     * List every ride, most recent first, with its cached distance, expected ride time and weather.
     */
    public function index(): JsonResponse
    {
        $rides = Ride::query()
            ->with('weather')
            ->select('rides.*')
            ->addSelect(['distance_meters' => RideRouteSubquery::distanceMeters()])
            ->addSelect(['expected_duration_seconds' => RideRouteSubquery::expectedDurationSeconds()])
            ->orderByDesc('checkout_time')
            ->orderByDesc('ride_id')
            ->get();

        return ApiJson::response([
            'results' => RideResource::collection($rides)->resolve(),
        ]);
    }

    /**
     * Aggregate duration and distance statistics across every ride.
     */
    public function summary(RideSummaryCalculator $calculator): JsonResponse
    {
        return ApiJson::response($calculator->calculate());
    }

    /**
     * The subscription cost per ride, and how it compares to buying passes.
     */
    public function cost(RideCostCalculator $calculator): JsonResponse
    {
        return ApiJson::response($calculator->calculate());
    }
}
