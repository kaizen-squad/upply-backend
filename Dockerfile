FROM dunglas/frankenphp:1-php8.4-alpine

RUN apk add --no-cache postgresql-client redis curl && \
    install-php-extensions \
    pdo_pgsql redis bcmath gd intl zip opcache

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Créer l'utilisateur AVANT le COPY
RUN addgroup -g 1000 unit && adduser -u 1000 -D -S -G unit unit

WORKDIR /app

COPY --chown=unit:unit . .

RUN chmod +x Docker/entry.sh

RUN mkdir -p storage/logs bootstrap/cache && \
    chown -R unit:unit storage bootstrap/cache

ENV PORT=8000 HOST=0.0.0.0

USER unit

ENTRYPOINT ["/bin/sh", "/app/Docker/entry.sh"]