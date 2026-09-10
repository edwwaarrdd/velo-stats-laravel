<?php

namespace App\Domain\Rides\Http\Controllers;

use App\Domain\Rides\Services\RideCostCalculator;
use App\Support\ApiJson;
use Illuminate\Http\JsonResponse;

class CalculateRideCostRequestHandler
{
    public function __construct(private readonly RideCostCalculator $costCalculator) {}

    public function __invoke(): JsonResponse
    {
        return ApiJson::response($this->costCalculator->calculate());
    }
}
