<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiJson
{
    /**
     * Laravel's defaults plus zero-fraction preservation, so a distance of
     * 1500.0 metres is not encoded as the integer 1500.
     */
    public const ENCODING_OPTIONS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION;

    /**
     * @param  array<string, mixed>  $data
     */
    public static function response(array $data, int $status = 200): JsonResponse
    {
        return response()->json($data, $status, [], self::ENCODING_OPTIONS);
    }
}
