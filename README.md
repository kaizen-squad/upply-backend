# TECHNICAL DOCUMENTATION

## Overview

This API REST is built in Laravel 12. It expose secured and well thought out endpoint for a minor freelance system.

## INTEGRATION STEPS

    - Clone the project:

        git clone git@github.com:kaizen-squad/upply-backend.git


    - Install dependencies:

        composer install

        npm install


    - Create the environment variables file:

        cp .env.example .env


    - Generate the application key:

        php artisan key:generate

## DOCKER SETUP

The project is dockerized using **FrankenPHP**. Most of the setup (Composer, Key Generation, Migrations) is automated via the internal entrypoint script.

- **1. Prepare the Environment File:**
    Copy the example file to the root directory. This is required so Docker Compose can inject the variables into the containers:
    cp .env.example .env

- **2. Prevent PostgreSQL Port Conflicts:**
    The Docker database container uses port `5432`. If you have a local PostgreSQL service running, it will block the container. Stop it before proceeding:
    - **Linux:** `sudo service postgresql stop`

    - **macOS:** `brew services stop postgresql`


- **3. Build and Start the Containers:**
    `docker compose up -d`
    - *Note: If you modify the `Dockerfile` later, use `docker compose up --build -d` to apply changes.*
    - *To ensure everything is going fine check endpoint* `/api/health` *on base url* `localhost:8000`


- **4. Finalize Setup (Assets & Seeding):**
    While PHP dependencies and migrations are handled automatically, you need to run the following for the frontend and data:
    
    `docker compose exec php php artisan db:seed`
    `docker compose exec php npm install`
    `docker compose exec php npm run dev`


- **5. Stopping the Services:**
    To stop the environment and remove the containers, run the following from the root directory:
    docker compose down