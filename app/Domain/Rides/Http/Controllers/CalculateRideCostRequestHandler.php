<?php

namespace App\Domain\Rides\Http\Controllers;

use App\Domain\Rides\Services\RideCostCalculator;
use App\Support\ApiJson;
use Illuminate\Http\JsonResponse;

class CalculateRideCostRequestHandler
{
    public function __construct(private readonly RideCostCalculator $costCalculator) {}

    /**
     * The subscription cost per ride, and how it compares to buying passes.
     */
    public function __invoke(): JsonResponse
    {
        return ApiJson::response($this->costCalculator->calculate());
    }
}
