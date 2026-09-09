<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class HealthcheckController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(['message' => 'ok']);
    }
}
