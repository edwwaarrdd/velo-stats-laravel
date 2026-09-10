<?php

namespace App\Http\Controllers;

use App\Support\ApiJson;
use Illuminate\Http\JsonResponse;

class HealthcheckRequestHandler
{
    public function __invoke(): JsonResponse
    {
        return ApiJson::response(['message' => 'ok']);
    }
}
