# Velo Stats

Laravel app for tracking Velo Antwerp bike-share stations, ride history, routing and weather.

Ride history is loaded from a JSON export, station information from the public Velo Antwerp GBFS feed. Each ride is
then enriched in the background: the cycling distance between its two stations comes from the public OSRM routing
API, and the weather at its origin station and checkin time comes from the free Open-Meteo archive. The API serves
the combined data as JSON.

## Setup

```
docker compose up -d --build
```

This starts five services:
- `app` – Laravel app served on port `8000`
- `redis` – queue backend
- `worker` – queue worker for the `default` queue
- `worker-ride-distance` – worker consuming the `ride_distance_checks` queue one job at a time, so calls to the free
  routing API are never made concurrently
- `worker-ride-weather` – worker consuming the `ride_weather_checks` queue one job at a time, so calls to the free
  Open-Meteo API are never made concurrently

Every container creates `.env` from `.env.example` on first start, generates an application key, and runs the
migrations, so no manual setup is needed.

Verify the app is up and running:

```
curl http://localhost:8000/_healthcheck
```

Should return a 200 OK response.

Stop everything with:

```
docker compose down
```

The project directory is bind-mounted into every container, so code edits are picked up without a rebuild.
Dependencies live in the image rather than the mount, so after changing `composer.json` rebuild and recreate:

```
docker compose up -d --build --force-recreate --renew-anon-volumes
```

### Loading data

A fresh database is empty. Populate it in this order:

```
docker compose exec app php artisan stations:load
docker compose exec app php artisan rides:load
docker compose exec app php artisan rides:check-distances
docker compose exec app php artisan rides:check-weather
```

The last two commands queue one job per ride and return immediately. The dedicated workers drain them one call at a
time, which takes a few minutes for a full ride history.

### Configuration

Environment variables (set in `docker-compose.yml`):

| Variable | Default | Description |
|---|---|---|
| `APP_DEBUG` | `false` | Enable Laravel debug mode |
| `APP_TIMEZONE` | `UTC` | Timezone all times are stored and rendered in |
| `QUEUE_CONNECTION` | `redis` | Queue driver |
| `REDIS_HOST` | `redis` | Redis host backing the queues |
| `CORS_ALLOWED_ORIGINS` | `http://localhost:5173` | Comma-separated origins allowed to call the API |

Data is persisted to a SQLite database at `database/database.sqlite`.

The three upstream endpoints are configured in `config/services.php` and can be overridden with
`VELO_ANTWERP_STATION_INFORMATION_URL`, `OSRM_BASE_URL` and `OPEN_METEO_ARCHIVE_URL`.

## API Endpoints

| Method | Path | Description |
|---|---|---|
| `GET` | `/_healthcheck` | Returns `{"message": "ok"}` with a 200 status if the app is up |
| `GET` | `/rides` | Returns every ride with its basic info, distance (from the cached station route), speed (distance ÷ duration), and cached weather, most recent first |
| `GET` | `/rides/summary` | Returns aggregate stats across all rides: total rides, total/average/longest/shortest duration, and total/average distance |
| `GET` | `/rides/cost` | Returns the cost per ride, using the € 58/year subscription price prorated over the date range from the first to the last ride, plus the equivalent cost and money saved versus paying with day passes (€ 5) or week passes (€ 12) instead |

## Console Commands

Run against the running `app` container:

```
docker compose exec app php artisan <command>
```

| Command | Description |
|---|---|
| `stations:load` | Fetches Velo Antwerp station information from the public GBFS feed and upserts it into the database |
| `rides:load [--path=PATH]` | Loads ride history from a JSON export (defaults to `data/rides.json`) and upserts it into the database |
| `tasks:dispatch-test [--message=MSG]` | Dispatches a test job that logs a message from the worker, useful for verifying the queue setup |
| `rides:check-distances` | Queues a job per unchecked ride to calculate and cache the distance between its origin and destination stations, one at a time via the `ride_distance_checks` queue |
| `rides:check-weather [--force]` | Queues a job per ride to fetch and cache the biking-relevant weather (temperature, precipitation, wind, cloud cover, humidity, weather code) at its origin station and checkin time from the free Open-Meteo API, one at a time via the `ride_weather_checks` queue. Only unchecked rides are queued by default; pass `--force` to re-fetch weather for every ride |

### Verifying the queue setup

Dispatch a test "hello world" job through the `app` container:

```
docker compose exec app php artisan tasks:dispatch-test --message "hello world"
```

Then check the `worker` container's logs to confirm the message was picked up and processed:

```
docker compose logs worker
```

You should see a log line containing `hello world` from the worker.

## Tests

```
php artisan test
```

The suite runs against an in-memory SQLite database and fakes every third-party call, so it never touches the
network or the development database.
