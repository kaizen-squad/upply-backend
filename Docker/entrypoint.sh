#!/bin/sh

if [ ! -f "vendor/autoload.php" ]; then
    composer install --no-progress --no-interaction
fi

if [ ! -f ".env" ]; then
    echo "Creating env file for en $APP_ENV"
    cp .env.example .env
else
    echo "env file exists"
fi

php artisan install:api

php artisan migrate

php artisan key:generate

php artisan cache:clear

php artisan config:clear

php artisan route:clear

php artisan serve --port=$PORT --host=$HOST --env=.env

exec docker-php-entrypoint "$@"