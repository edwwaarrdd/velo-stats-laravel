#!/usr/bin/env bash
set -e

# Laravel needs an application key and reads it from the environment. In a real
# deployment APP_KEY is injected as a secret; here one is generated on first
# start so the image ships without a baked-in key.
if [ ! -f /app/.env ]; then
    cp /app/.env.example /app/.env
fi

if ! grep -qE '^APP_KEY=.+' /app/.env; then
    php artisan key:generate --force --no-interaction
fi

# Resolving config and routes from source on every request is a development
# behaviour. Caching them collapses that work into one file each.
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction

exec "$@"
