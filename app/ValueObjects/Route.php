<?php

namespace App\ValueObjects;

readonly class Route
{
    public function __construct(
        public float $distanceMeters,
        public float $durationSeconds,
    ) {}

    /**
     * @param  array{distance: float|int, duration: float|int}  $route
     */
    public static function fromOsrmRoute(array $route): self
    {
        return new self(
            distanceMeters: (float) $route['distance'],
            durationSeconds: (float) $route['duration'],
        );
    }
}
