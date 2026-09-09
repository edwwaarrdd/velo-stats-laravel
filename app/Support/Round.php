<?php

namespace App\Support;

/**
 * Rounds monetary and statistical values to the two decimals the API reports.
 */
class Round
{
    public const PRECISION = 2;

    /**
     * Round to two decimals.
     *
     * This formats rather than calling round(), because round() first nudges
     * the value towards the decimal a human would have typed: it treats
     * 15.995, whose nearest double is really 15.99499999999999957, as an exact
     * midpoint and rounds it up to 16.0. Formatting rounds the actual double,
     * so that value reports as 15.99.
     */
    public static function money(int|float|null $value): ?float
    {
        return $value === null
            ? null
            : (float) sprintf('%.'.self::PRECISION.'F', $value);
    }
}
