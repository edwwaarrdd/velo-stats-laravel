<?php

namespace App\Support;

/**
 * Rounds monetary and statistical values to the two decimals the API reports.
 */
class Round
{
    public const PRECISION = 2;

    /**
     * Round half to even, so results match the values the API has always
     * reported for exact midpoints.
     */
    public static function money(int|float|null $value): ?float
    {
        return $value === null
            ? null
            : round((float) $value, self::PRECISION, PHP_ROUND_HALF_EVEN);
    }
}
