<?php

use App\Jobs\CheckRideDistance;
use App\Jobs\CheckRideWeather;
use App\Jobs\LogTestMessage;
use App\Models\Ride;
use Illuminate\Support\Facades\Queue;

beforeEach(fn () => Queue::fake());

it('dispatches a distance check for every unchecked ride', function (): void {
    Ride::factory()->count(2)->create();
    Ride::factory()->create(['distance_checked_at' => '2026-01-01 00:00:00']);

    $this->artisan('rides:check-distances')
        ->expectsOutputToContain('Dispatched 2 ride distance check task(s).')
        ->assertSuccessful();

    Queue::assertPushedOn('ride_distance_checks', CheckRideDistance::class);
    Queue::assertPushed(CheckRideDistance::class, 2);
});

it('dispatches a weather check for every unchecked ride', function (): void {
    Ride::factory()->count(2)->create();
    Ride::factory()->create(['weather_checked_at' => '2026-01-01 00:00:00']);

    $this->artisan('rides:check-weather')
        ->expectsOutputToContain('Dispatched 2 ride weather check task(s).')
        ->assertSuccessful();

    Queue::assertPushedOn('ride_weather_checks', CheckRideWeather::class);
    Queue::assertPushed(CheckRideWeather::class, 2);
});

it('dispatches a weather check for every ride when forced', function (): void {
    Ride::factory()->count(2)->create();
    Ride::factory()->create(['weather_checked_at' => '2026-01-01 00:00:00']);

    $this->artisan('rides:check-weather', ['--force' => true])
        ->expectsOutputToContain('Dispatched 3 ride weather check task(s).')
        ->assertSuccessful();

    Queue::assertPushed(CheckRideWeather::class, 3);
});

it('dispatches a test task onto the default queue', function (): void {
    $this->artisan('tasks:dispatch-test', ['--message' => 'hello world'])
        ->expectsOutputToContain('Dispatched a test task to the queue.')
        ->assertSuccessful();

    Queue::assertPushed(LogTestMessage::class, 1);
});
