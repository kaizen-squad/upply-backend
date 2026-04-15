FROM dunglas/frankenphp:1-php8.4-alpine
# Installation des dépendances système et extensions PHP
<<<<<<< HEAD
RUN apk add --no-cache postgresql-client redis curl && \
    install-php-extensions \
=======
RUN install-php-extensions \
>>>>>>> 3e1eb8b (feat- Basis backend dockerization)
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

<<<<<<< HEAD
<<<<<<< HEAD
ENV PORT=8000
=======
ENV PORT=8000 HOST=0.0.0.0
>>>>>>> 3e1eb8b (feat- Basis backend dockerization)
=======
ENV PORT=8000
>>>>>>> 45d8cb3 (fix- Review of pull request #33 taking in account)

# Création de l'utilisateur unit
RUN addgroup -g 1000 unit && adduser -u 1000 -D -S -G unit unit

# Droits sur les dossiers de stockage Laravel
RUN chown -R unit:unit storage bootstrap/cache