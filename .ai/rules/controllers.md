---
paths:
  - 'app/**/Http/Controllers/**'
---

# Controllers

## Single-action request handlers, not multi-action Controllers
Endpoints are one plain, invokable class per action — no base `Controller` class (it has been removed; nothing extends it). Name each class `VerbNounRequestHandler` (e.g. `ListRidesRequestHandler`, `SummarizeRidesRequestHandler`) with a single `__invoke()` method, declare dependencies via a promoted constructor, and register it directly in `routes/api.php` as `Route::get('/path', SomeRequestHandler::class)`. These classes still live under each domain's `Http/Controllers` folder (folder name kept for now) or `app/Http/Controllers` for cross-cutting endpoints like the healthcheck.

Persistence access from these handlers (and from console commands, jobs, and services) goes through a repository interface, not the Eloquent model directly — see the `Contracts`/`Repositories` rule for `app/Domain/**`.
