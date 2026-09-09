<?php

use App\Domain\Rides\Http\Controllers\RideController;
use App\Http\Controllers\HealthcheckController;
use Illuminate\Support\Facades\Route;

Route::get('/_healthcheck', HealthcheckController::class);

Route::prefix('rides')->name('rides.')->group(function (): void {
    Route::get('/', [RideController::class, 'index'])->name('index');
    Route::get('/summary', [RideController::class, 'summary'])->name('summary');
    Route::get('/cost', [RideController::class, 'cost'])->name('cost');
});
