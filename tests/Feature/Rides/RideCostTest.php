<?php

use App\Domain\Rides\Models\Ride;

it('reports no cost breakdown when there are no rides', function (): void {
    $this->getJson('/rides/cost')->assertOk()->assertExactJson([
        'total_rides' => 0,
        'first_ride_date' => null,
        'last_ride_date' => null,
        'date_range_days' => null,
        'subscription_price_eur' => 58.0,
        'prorated_subscription_price_eur' => null,
        'cost_per_ride_eur' => null,
        'day_pass_equivalent_eur' => null,
        'week_pass_equivalent_eur' => null,
        'money_saved_vs_day_passes_eur' => null,
        'money_saved_vs_week_passes_eur' => null,
    ]);
});

it('prorates the subscription over the ride date range', function (): void {
    Ride::factory()->create(['checkout_time' => '2026-01-01 08:00:00']);
    Ride::factory()->create(['checkout_time' => '2026-01-01 12:00:00']);
    Ride::factory()->create(['checkout_time' => '2026-01-10 08:00:00']);

    $this->getJson('/rides/cost')->assertOk()->assertExactJson([
        'total_rides' => 3,
        'first_ride_date' => '2026-01-01',
        'last_ride_date' => '2026-01-10',
        'date_range_days' => 10,
        'subscription_price_eur' => 58.0,
        'prorated_subscription_price_eur' => 1.59,
        'cost_per_ride_eur' => 0.53,
        'day_pass_equivalent_eur' => 10.0,
        'week_pass_equivalent_eur' => 24.0,
        'money_saved_vs_day_passes_eur' => 8.41,
        'money_saved_vs_week_passes_eur' => 22.41,
    ]);
});

it('counts a single ride as a one day range', function (): void {
    Ride::factory()->create(['checkout_time' => '2026-05-04 09:30:00']);

    $cost = $this->getJson('/rides/cost')->json();

    expect($cost['date_range_days'])->toBe(1)
        ->and($cost['first_ride_date'])->toBe('2026-05-04')
        ->and($cost['last_ride_date'])->toBe('2026-05-04')
        ->and($cost['prorated_subscription_price_eur'])->toBe(0.16)
        ->and($cost['cost_per_ride_eur'])->toBe(0.16)
        ->and($cost['day_pass_equivalent_eur'])->toBe(5.0)
        ->and($cost['week_pass_equivalent_eur'])->toBe(12.0);
});

it('counts rides in the same ISO week only once', function (): void {
    // Monday and Sunday of the same ISO week, so two ride days but one week.
    Ride::factory()->create(['checkout_time' => '2026-03-02 08:00:00']);
    Ride::factory()->create(['checkout_time' => '2026-03-08 08:00:00']);

    $cost = $this->getJson('/rides/cost')->json();

    expect($cost['day_pass_equivalent_eur'])->toBe(10.0)
        ->and($cost['week_pass_equivalent_eur'])->toBe(12.0);
});

it('counts ride days from the checkout time in UTC', function (): void {
    Ride::factory()->create(['checkout_time' => '2026-06-01 23:30:00']);
    Ride::factory()->create(['checkout_time' => '2026-06-02 00:30:00']);

    $cost = $this->getJson('/rides/cost')->json();

    expect($cost['first_ride_date'])->toBe('2026-06-01')
        ->and($cost['last_ride_date'])->toBe('2026-06-02')
        ->and($cost['date_range_days'])->toBe(2);
});
