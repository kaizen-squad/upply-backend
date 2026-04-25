FROM dunglas/frankenphp:1-php8.4-alpine

# 1. Install system dependencies
RUN apk add --no-cache postgresql-client redis curl && \
    install-php-extensions pdo_pgsql redis bcmath gd intl zip opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# 2. Copier les fichiers de dépendances d'abord pour optimiser le cache Docker
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction

# 3. Copier le reste du projet
COPY . .

# 4. Finir l'installation de composer
RUN composer dump-autoload --optimize --no-dev

# 5. Droits d'exécution et permissions
# Sur Render, il vaut mieux rester en root ou laisser Render gérer l'user, 
# mais FrankenPHP a besoin de droits sur /data (pour Caddy)
RUN chmod +x Docker/entry.sh && \
    mkdir -p storage/logs bootstrap/cache && \
    chmod -R 777 storage bootstrap/cache

ENV PORT=8000 HOST=0.0.0.0

ENTRYPOINT ["/app/Docker/entry.sh"]