#!/bin/sh
set -e

if [ -z "$APP_KEY" ]; then
  echo "ERROR: APP_KEY no esta definida en .env"
  exit 1
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache || true

php artisan l5-swagger:generate || echo "Aviso: no se pudo generar Swagger"

if [ "$RUN_MIGRATIONS" = "true" ]; then
  php artisan migrate --force
fi

exec "$@"