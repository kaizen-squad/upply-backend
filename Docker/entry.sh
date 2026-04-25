#!/bin/sh

if [ ! -f "vendor/autoload.php" ]; then
    composer install --no-progress --no-interaction --no-dev
fi

echo "Waiting for database ($DB_HOST:$DB_PORT)..."
until pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME"; do
    echo "Database is unavailable - sleeping"
    sleep 2
done
echo "Database is up!"

php artisan migrate --force

# En prod on cache, on ne clear pas
php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R unit:unit storage bootstrap/cache vendor

exec frankenphp run --config /app/Docker/Caddyfile