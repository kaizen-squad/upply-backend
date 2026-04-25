#!/bin/sh
set -e # Arrête le script en cas d'erreur

# On ne fait plus de composer install ici, il est déjà dans l'image !

if [ ! -f ".env" ]; then
    cp .env.example .env
fi

# On attend la DB
until pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME"; do
    echo "Waiting for database..."
    sleep 2
done

# On lance les migrations
php artisan migrate --force

# Suppression des chown (inutiles/interdits sur bcp de PaaS en runtime)
# Suppression des caches
php artisan config:cache
php artisan route:cache

echo "🚀 Starting FrankenPHP..."

# ✅ Utilisation du port dynamique de Render
exec frankenphp run --config /app/Docker/Caddyfile --listen :$PORT