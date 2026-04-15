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

=======
>>>>>>> ab425dd (fix- Review of pull request #33 taking in account)
php artisan migrate

if ! grep -Eq '^APP_KEY=.+$' .env; then
    echo "APP_KEY absente, génération de la clé applicative"
    php artisan key:generate
else
    echo "APP_KEY déjà définie, aucune régénération"
fi

php artisan cache:clear

php artisan config:clear

php artisan route:clear

# Ajuster les droits pour l'utilisateur unit
chown -R unit:unit storage bootstrap/cache vendor

<<<<<<< HEAD
<<<<<<< HEAD
exec docker-php-entrypoint "$@"
>>>>>>> 3e1eb8b (feat- Basis backend dockerization)
=======
php artisan serve --port=$PORT --host=0.0.0.0 --env=local
>>>>>>> ab425dd (fix- Review of pull request #33 taking in account)
=======
php artisan serve --port=$PORT --host=0.0.0.0 --env=$APP_ENV
>>>>>>> fdddfe8 (fix- Dockerfile key generation)
