FROM dunglas/frankenphp:1-php8.4-alpine

# Supprime les capacités étendues qui font peur à Render
RUN setcap -r /usr/local/bin/frankenphp || true

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

# Générer l'autoloader optimisé pour la production
RUN composer dump-autoload --no-dev --optimize

# S'assurer que le script d'entrée est exécutable
RUN chmod +x /app/Docker/entry.sh

ENV PORT=8000 HOST=0.0.0.0

ENTRYPOINT ["/bin/sh", "/app/Docker/entry.sh"]