<?php

namespace App\Domain\Rides\Http\Controllers;

use App\Domain\Rides\Services\RideSummaryCalculator;
use App\Support\ApiJson;
use Illuminate\Http\JsonResponse;

class SummarizeRidesRequestHandler
{
    public function __construct(private readonly RideSummaryCalculator $summaryCalculator) {}

    /**
     * Aggregate duration and distance statistics across every ride.
     */
    public function __invoke(): JsonResponse
    {
        return ApiJson::response($this->summaryCalculator->calculate());
    }
}
