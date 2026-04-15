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

php artisan migrate

php artisan key:generate

php artisan cache:clear

php artisan config:clear

php artisan route:clear

# Ajuster les droits pour l'utilisateur unit
chown -R unit:unit storage bootstrap/cache vendor

php artisan serve --port=$PORT --host=0.0.0.0 --env=local