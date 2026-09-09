<?php

namespace App\Support;

readonly class Coordinate
{
    public function __construct(
        public float $lat,
        public float $lon,
    ) {}
}
