<?php

use App\Support\Round;

it('passes null through', function (): void {
    expect(Round::money(null))->toBeNull();
});

it('rounds to two decimals', function (): void {
    expect(Round::money(1.234))->toBe(1.23)
        ->and(Round::money(1.236))->toBe(1.24)
        ->and(Round::money(10))->toBe(10.0);
});

it('rounds the value the double actually holds, not the one that was typed', function (): void {
    // 1599.5 metres in 6 minutes is 15.995 km/h, whose nearest double is just
    // under the midpoint, so it rounds down.
    expect(Round::money((1599.5 / 1000) / (6 / 60)))->toBe(15.99)
        ->and(Round::money(2.675))->toBe(2.67)
        ->and(Round::money(1.585))->toBe(1.58);
});

it('rounds an exact midpoint to the even digit', function (): void {
    expect(Round::money(0.125))->toBe(0.12)
        ->and(Round::money(0.375))->toBe(0.38);
});
