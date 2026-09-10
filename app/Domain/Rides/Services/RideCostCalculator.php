<?php

namespace App\Domain\Rides\Services;

use App\Domain\Rides\Contracts\RideRepository;
use App\Support\ApiDateTime;
use App\Support\Round;
use Illuminate\Support\Carbon;

class RideCostCalculator
{
    public const ANNUAL_SUBSCRIPTION_PRICE_EUR = 58.0;

    public const DAYS_PER_YEAR = 365;

    public const DAY_PASS_PRICE_EUR = 5.0;

    public const WEEK_PASS_PRICE_EUR = 12.0;

    public function __construct(private readonly RideRepository $rides) {}

    /**
     * @return array<string, int|float|string|null>
     */
    public function calculate(): array
    {
        $checkoutTimes = $this->rides
            ->checkoutTimes()
            ->map(fn (Carbon $checkoutTime): Carbon => $checkoutTime->utc());

        $totalRides = $checkoutTimes->count();

        if ($totalRides === 0) {
            return $this->emptySummary();
        }

        $firstRideDate = $checkoutTimes->min()->copy()->startOfDay();
        $lastRideDate = $checkoutTimes->max()->copy()->startOfDay();
        $dateRangeDays = (int) $firstRideDate->diffInDays($lastRideDate) + 1;

        $proratedSubscriptionPrice = Round::money(
            self::ANNUAL_SUBSCRIPTION_PRICE_EUR * $dateRangeDays / self::DAYS_PER_YEAR
        );
        $costPerRide = Round::money($proratedSubscriptionPrice / $totalRides);

        $rideDays = $checkoutTimes
            ->map(fn (Carbon $checkoutTime): string => $checkoutTime->toDateString())
            ->unique()
            ->count();
        $rideWeeks = $checkoutTimes
            ->map(fn (Carbon $checkoutTime): string => $checkoutTime->isoWeekYear.'-'.$checkoutTime->isoWeek)
            ->unique()
            ->count();

        $dayPassEquivalent = Round::money($rideDays * self::DAY_PASS_PRICE_EUR);
        $weekPassEquivalent = Round::money($rideWeeks * self::WEEK_PASS_PRICE_EUR);

        return [
            'total_rides' => $totalRides,
            'first_ride_date' => ApiDateTime::date($firstRideDate),
            'last_ride_date' => ApiDateTime::date($lastRideDate),
            'date_range_days' => $dateRangeDays,
            'subscription_price_eur' => self::ANNUAL_SUBSCRIPTION_PRICE_EUR,
            'prorated_subscription_price_eur' => $proratedSubscriptionPrice,
            'cost_per_ride_eur' => $costPerRide,
            'day_pass_equivalent_eur' => $dayPassEquivalent,
            'week_pass_equivalent_eur' => $weekPassEquivalent,
            'money_saved_vs_day_passes_eur' => Round::money($dayPassEquivalent - $proratedSubscriptionPrice),
            'money_saved_vs_week_passes_eur' => Round::money($weekPassEquivalent - $proratedSubscriptionPrice),
        ];
    }

    /**
     * @return array<string, int|float|null>
     */
    private function emptySummary(): array
    {
        return [
            'total_rides' => 0,
            'first_ride_date' => null,
            'last_ride_date' => null,
            'date_range_days' => null,
            'subscription_price_eur' => self::ANNUAL_SUBSCRIPTION_PRICE_EUR,
            'prorated_subscription_price_eur' => null,
            'cost_per_ride_eur' => null,
            'day_pass_equivalent_eur' => null,
            'week_pass_equivalent_eur' => null,
            'money_saved_vs_day_passes_eur' => null,
            'money_saved_vs_week_passes_eur' => null,
        ];
    }
}
