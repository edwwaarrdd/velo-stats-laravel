<?php

namespace App\Providers;

use App\Contracts\RideDataSource;
use App\Contracts\RouteService;
use App\Contracts\StationInformationService;
use App\Contracts\WeatherService;
use App\Services\JsonFileRideService;
use App\Services\OpenMeteoWeatherService;
use App\Services\OsrmRouteService;
use App\Services\VeloAntwerpStationInformationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(StationInformationService::class, fn (): VeloAntwerpStationInformationService => new VeloAntwerpStationInformationService(
            config('services.velo_antwerp.station_information_url'),
        ));

        $this->app->bind(RouteService::class, fn (): OsrmRouteService => new OsrmRouteService(
            config('services.osrm.base_url'),
        ));

        $this->app->bind(WeatherService::class, fn (): OpenMeteoWeatherService => new OpenMeteoWeatherService(
            config('services.open_meteo.archive_url'),
        ));

        $this->app->bind(RideDataSource::class, fn (): JsonFileRideService => new JsonFileRideService(
            base_path(config('services.rides.json_path')),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());
    }
}
