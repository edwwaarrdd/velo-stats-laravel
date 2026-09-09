<?php

namespace App\ValueObjects;

readonly class Coordinate
{
    public function __construct(
        public float $lat,
        public float $lon,
    ) {}
}
