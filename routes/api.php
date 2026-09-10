<?php

use App\Domain\Rides\Http\Controllers\CalculateRideCostRequestHandler;
use App\Domain\Rides\Http\Controllers\ListRidesRequestHandler;
use App\Domain\Rides\Http\Controllers\SummarizeRidesRequestHandler;
use App\Domain\Stations\Http\Controllers\ListStationsRequestHandler;
use App\Http\Controllers\HealthcheckRequestHandler;
use Illuminate\Support\Facades\Route;

Route::get('/_healthcheck', HealthcheckRequestHandler::class);

Route::prefix('rides')->name('rides.')->group(function (): void {
    Route::get('/', ListRidesRequestHandler::class)->name('index');
    Route::get('/summary', SummarizeRidesRequestHandler::class)->name('summary');
    Route::get('/cost', CalculateRideCostRequestHandler::class)->name('cost');
});

Route::get('/stations', ListStationsRequestHandler::class)->name('stations.index');
