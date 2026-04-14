#!/bin/sh

if [ ! -f "vendor/autoload.php" ]; then
    composer install --no-progress --no-interaction
fi

if [ ! -f ".env" ]; then
<<<<<<< HEAD
    echo "Creating env file for $APP_ENV"
    cp .env.example .env
    # Écrire APP_KEY dans .env si fournie en env var
    if [ -n "$APP_KEY" ]; then
        sed -i "s|APP_KEY=.*|APP_KEY=$APP_KEY|" .env
    fi
=======
    echo "Creating env file for en $APP_ENV"
    cp .env.example .env
>>>>>>> 3e1eb8b (feat- Basis backend dockerization)
else
    echo "env file exists"
fi

<<<<<<< HEAD
echo "Waiting for database ($DB_HOST:$DB_PORT)..."
until pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME"; do
    echo "Database is unavailable - sleeping"
    sleep 2
done
echo "Database is up!"

php artisan migrate --force
php artisan cache:clear
php artisan config:clear
php artisan route:clear

chown -R unit:unit storage bootstrap/cache vendor

# ✅ Utiliser FrankenPHP, pas artisan serve
exec frankenphp run --config /app/Docker/Caddyfile
=======
php artisan install:api

php artisan migrate

php artisan key:generate

php artisan cache:clear

php artisan config:clear

php artisan route:clear

php artisan serve --port=$PORT --host=$HOST --env=.env

exec docker-php-entrypoint "$@"
>>>>>>> 3e1eb8b (feat- Basis backend dockerization)
