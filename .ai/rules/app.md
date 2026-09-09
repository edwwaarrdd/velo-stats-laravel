---
paths:
  - 'app/**'
---

# App

## Group application code by domain, not by type
Application code lives in `app/Domain/<Name>/` (Rides, Stations, Routing, Weather), each holding its own Models, Contracts, Services, Jobs, Console/Commands and Http. New code belongs in a domain, not in `app/Models`, `app/Services` or `app/Contracts` — those folders no longer exist.

Only genuinely cross-cutting code stays outside: `app/Support` (ApiJson, ApiDateTime, Round, Coordinate), the abstract `Controller` and the healthcheck in `app/Http`, and the queue smoke test (`LogTestMessage`, `DispatchTestTask`).

Two pieces of wiring keep this working, so do not remove them:
- `bootstrap/app.php` registers `->withCommands([__DIR__.'/../app/Domain'])`. Laravel only auto-discovers commands in `app/Console/Commands`, so without it every domain command silently disappears from `php artisan list`.
- `AppServiceProvider::boot()` calls `Factory::guessFactoryNamesUsing()` to map a model to `Database\Factories\<Model>Factory`. Factories and migrations stay in their standard `database/` locations; the default path-based guess would look for a factory under a domain sub-namespace.
