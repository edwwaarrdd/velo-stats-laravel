---
paths:
  - 'app/**'
---

# App

## Group application code by domain, not by type
Application code lives in `app/Domain/<Name>/` (Rides, Stations, Routing, Weather), each holding its own Models, Contracts, Services, Jobs, Console/Commands and Http. New code belongs in a domain, not in `app/Models`, `app/Services` or `app/Contracts` — those folders no longer exist.

Only genuinely cross-cutting code stays outside: `app/Support` (ApiJson, ApiDateTime, Round, Coordinate), the healthcheck request handler in `app/Http`, and the queue smoke test (`LogTestMessage`, `DispatchTestTask`).

Two pieces of wiring keep this working, so do not remove them:
- `bootstrap/app.php` registers `->withCommands([__DIR__.'/../app/Domain'])`. Laravel only auto-discovers commands in `app/Console/Commands`, so without it every domain command silently disappears from `php artisan list`.
- `AppServiceProvider::boot()` calls `Factory::guessFactoryNamesUsing()` to map a model to `Database\Factories\<Model>Factory`. Factories and migrations stay in their standard `database/` locations; the default path-based guess would look for a factory under a domain sub-namespace.

## Inject dependencies, do not reach for the container
Declare every dependency in a promoted constructor and resolve nothing at the point of use. No facades in `app/` (no `Http`, `Log`, `DB`), no `app()`, `resolve()` or `App::make()`. Inject the framework equivalents instead: `Illuminate\Http\Client\Factory` for HTTP calls, `Psr\Log\LoggerInterface` for logging, `Illuminate\Database\DatabaseManager` for raw query building.

Controllers and console commands take their collaborators in `__construct`, not as action-method or `handle()` parameters. A command with a constructor must call `parent::__construct()`.

Queued jobs are the one exception: their `handle()` still receives services as parameters, because a job is serialised onto the queue between dispatch and execution and a service held as a property would be serialised with it.

`AppServiceProvider::register()` is the composition root and the only place that resolves from the container. It builds each service explicitly, passing the HTTP client and the configuration values in. `config()` and `base_path()` are fine there.

The facade test helpers still work with injected instances. `Http::fake()` and `Log::shouldReceive()` swap the same container singleton the constructor was given.
