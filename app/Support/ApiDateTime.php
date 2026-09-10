<?php

namespace App\Support;

use DateTimeInterface;
use Illuminate\Support\Carbon;

class ApiDateTime
{
    public const DATETIME_FORMAT = 'Y-m-d\TH:i:s\Z';

    public const DATE_FORMAT = 'Y-m-d';

    public static function datetime(?DateTimeInterface $value): ?string
    {
        return $value === null
            ? null
            : Carbon::instance($value)->utc()->format(self::DATETIME_FORMAT);
    }

    public static function date(?DateTimeInterface $value): ?string
    {
        return $value === null
            ? null
            : Carbon::instance($value)->utc()->format(self::DATE_FORMAT);
    }
}
