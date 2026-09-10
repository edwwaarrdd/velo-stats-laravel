---
paths:
  - 'app/Domain/**'
---

# Domain

## Access models through a repository interface, not directly
Every domain model (`Ride`, `Station`, `StationRoute`, `WeatherRecord`) is accessed through a repository interface, never by calling the model class statically (`Model::query()`, `Model::create()`, etc.) from controllers, commands, jobs, or services.

Each domain that owns a model defines an interface in `Contracts/<Model>Repository.php` and an Eloquent implementation in `Repositories/Eloquent<Model>Repository.php`, bound in `AppServiceProvider::register()` (plain `bind(Interface::class, Implementation::class)` since these need no constructor args). This mirrors the existing `Contracts` + `Services` pattern used for external services (`RouteService`, `WeatherService`, etc.).

Queued jobs still receive repositories as `handle()` parameters, same as any other service, per the constructor-injection exception for jobs.
