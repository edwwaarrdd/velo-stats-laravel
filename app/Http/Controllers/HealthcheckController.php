<?php

namespace App\Http\Controllers;

use App\Support\ApiJson;
use Illuminate\Http\JsonResponse;

class HealthcheckController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return ApiJson::response(['message' => 'ok']);
    }
}
