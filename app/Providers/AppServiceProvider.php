<?php

namespace App\Providers;

use App\Domain\Rides\Contracts\RideDataSource;
use App\Domain\Rides\Services\JsonFileRideService;
use App\Domain\Routing\Contracts\RouteService;
use App\Domain\Routing\Services\OsrmRouteService;
use App\Domain\Stations\Contracts\StationInformationService;
use App\Domain\Stations\Services\VeloAntwerpStationInformationService;
use App\Domain\Weather\Contracts\WeatherService;
use App\Domain\Weather\Services\OpenMeteoWeatherService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\Factory as HttpClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(StationInformationService::class, fn (Application $app): VeloAntwerpStationInformationService => new VeloAntwerpStationInformationService(
            $app->make(HttpClient::class),
            config('services.velo_antwerp.station_information_url'),
        ));

        $this->app->bind(RouteService::class, fn (Application $app): OsrmRouteService => new OsrmRouteService(
            $app->make(HttpClient::class),
            config('services.osrm.base_url'),
        ));

        $this->app->bind(WeatherService::class, fn (Application $app): OpenMeteoWeatherService => new OpenMeteoWeatherService(
            $app->make(HttpClient::class),
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
