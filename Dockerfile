FROM dunglas/frankenphp:1-php8.4-alpine
# Installation des dépendances système et extensions PHP
RUN install-php-extensions \
    pdo_pgsql \
    redis \
    bcmath \
    gd \
    intl \
    zip \
    opcache

# Installation de Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configuration du répertoire de travail
WORKDIR /app

# Copie des fichiers du projet
COPY . .

# Permissions sur l'entrypoint
RUN chmod +x Docker/entrypoint.sh

ENTRYPOINT [ "/bin/sh", "/app/Docker/entrypoint.sh" ]

ENV PORT=8000 HOST=0.0.0.0

# Création de l'utilisateur unit
RUN addgroup -g 1000 unit && adduser -u 1000 -D -S -G unit unit

# Droits sur les dossiers de stockage Laravel
RUN chown -R unit:unit storage bootstrap/cache