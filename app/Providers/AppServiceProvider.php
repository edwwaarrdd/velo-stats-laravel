<?php

namespace App\Providers;

use App\Contracts\RideDataSource;
use App\Contracts\WeatherService;
use App\Domain\Routing\Contracts\RouteService;
use App\Domain\Routing\Services\OsrmRouteService;
use App\Domain\Stations\Contracts\StationInformationService;
use App\Domain\Stations\Services\VeloAntwerpStationInformationService;
use App\Services\JsonFileRideService;
use App\Services\OpenMeteoWeatherService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
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

    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());

        /**
         * Models live in domain folders rather than App\Models, so Laravel's
         * default guess would look for a factory under a matching sub-namespace
         * of Database\Factories. Every factory sits directly in that namespace
         * and is named after its model instead.
         */
        Factory::guessFactoryNamesUsing(
            fn (string $modelName): string => 'Database\\Factories\\'.class_basename($modelName).'Factory',
        );
    }
}
