<?php

namespace App\Domain\Rides\Http\Controllers;

use App\Domain\Rides\Http\Resources\RideResource;
use App\Domain\Rides\Models\Ride;
use App\Domain\Rides\Services\RideCostCalculator;
use App\Domain\Rides\Services\RideRouteSubquery;
use App\Domain\Rides\Services\RideSummaryCalculator;
use App\Http\Controllers\Controller;
use App\Support\ApiJson;
use Illuminate\Http\JsonResponse;

class RideController extends Controller
{
    public function __construct(
        private readonly RideSummaryCalculator $summaryCalculator,
        private readonly RideCostCalculator $costCalculator,
    ) {}

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
    public function summary(): JsonResponse
    {
        return ApiJson::response($this->summaryCalculator->calculate());
    }

    /**
     * The subscription cost per ride, and how it compares to buying passes.
     */
    public function cost(): JsonResponse
    {
        return ApiJson::response($this->costCalculator->calculate());
    }
}
