<?php

namespace App\Providers;

use App\Domain\Rides\Contracts\RideDataSource;
use App\Domain\Rides\Contracts\RideRepository;
use App\Domain\Rides\Repositories\EloquentRideRepository;
use App\Domain\Rides\Services\JsonFileRideService;
use App\Domain\Routing\Contracts\RouteService;
use App\Domain\Routing\Contracts\StationRouteRepository;
use App\Domain\Routing\Repositories\EloquentStationRouteRepository;
use App\Domain\Routing\Services\OsrmRouteService;
use App\Domain\Stations\Contracts\StationInformationService;
use App\Domain\Stations\Contracts\StationRepository;
use App\Domain\Stations\Repositories\EloquentStationRepository;
use App\Domain\Stations\Services\VeloAntwerpStationInformationService;
use App\Domain\Weather\Contracts\WeatherRecordRepository;
use App\Domain\Weather\Contracts\WeatherService;
use App\Domain\Weather\Repositories\EloquentWeatherRecordRepository;
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

        $this->app->bind(RideRepository::class, EloquentRideRepository::class);
        $this->app->bind(StationRepository::class, EloquentStationRepository::class);
        $this->app->bind(StationRouteRepository::class, EloquentStationRouteRepository::class);
        $this->app->bind(WeatherRecordRepository::class, EloquentWeatherRecordRepository::class);
    }

    public function boot(): void
    {
        Model::shouldBeStrict(! $this->app->isProduction());

        Factory::guessFactoryNamesUsing(
            fn (string $modelName): string => 'Database\\Factories\\'.class_basename($modelName).'Factory',
        );
    }
}
