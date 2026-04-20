#!/bin/sh

if [ ! -f "vendor/autoload.php" ]; then
    composer install --no-progress --no-interaction
fi

if [ ! -f ".env" ]; then

    echo "Creating env file for $APP_ENV"
    cp .env.example .env
    # Écrire APP_KEY dans .env si fournie en env var
    if [ -n "$APP_KEY" ]; then
        sed -i "s|APP_KEY=.*|APP_KEY=$APP_KEY|" .env
    fi
else
    echo "env file exists"
fi

echo "Waiting for database ($DB_HOST:$DB_PORT)..."
until pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME"; do
    echo "Database is unavailable - sleeping"
    sleep 2
done
echo "Database is up!"


if ! grep -Eq '^APP_KEY=.+$' .env; then
    echo "APP_KEY absente, génération de la clé applicative"
    php artisan key:generate
else
    echo "APP_KEY déjà définie, aucune régénération"
fi

php artisan migrate --force
php artisan cache:clear
php artisan config:clear
php artisan route:clear

chown -R unit:unit storage bootstrap/cache vendor

# ✅ Utiliser FrankenPHP, pas artisan serve
exec frankenphp run --config /app/Docker/Caddyfile