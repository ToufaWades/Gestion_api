#!/bin/sh

# Basic runtime sanity checks for required DB envs
echo "Starting docker-entrypoint.sh"
MISSING=0
for v in DB_HOST DB_PORT DB_DATABASE DB_USERNAME; do
  eval val="\$$v"
  if [ -z "$val" ]; then
    echo "Required env $v is not set"
    MISSING=1
  fi
done

if [ "$MISSING" -eq 1 ]; then
  echo "One or more required DB env vars are missing. Aborting to avoid infinite wait." >&2
  exit 1
fi

# Generate APP_KEY if not provided via env
if [ -z "$APP_KEY" ]; then
  echo "APP_KEY not set — generating one"
  php artisan key:generate --force || true
fi

echo "Waiting for database to be ready..."
MAX_WAIT=120
WAITED=0
while ! pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" >/dev/null 2>&1; do
  if [ "$WAITED" -ge "$MAX_WAIT" ]; then
    echo "Timeout waiting for database after ${MAX_WAIT}s" >&2
    exit 1
  fi
  echo "Database is unavailable - sleeping 1s (waited=${WAITED}s)"
  sleep 1
  WAITED=$((WAITED+1))
done

echo "Database is up - executing migrations"
# Clear caches to avoid serving stale routes/config from image build
php artisan route:clear || true
php artisan config:clear || true
php artisan cache:clear || true

# Run migrations (non-blocking failure allowed)
php artisan migrate --force || true

echo "Starting Laravel application..."
exec "$@"
