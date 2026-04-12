<?php

<<<<<<< HEAD
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Application;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
=======
<<<<<<< HEAD
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
>>>>>>> 17ff392 (feat(architecture): Set up the backend code base structure)
=======
>>>>>>> 64bc454 (chore(architecture): Opened the api configuration for laravel)
=======
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
>>>>>>> 5e55ab6 (chore(architecture): Opened the api configuration for laravel)
<<<<<<< HEAD
>>>>>>> 64bc454 (chore(architecture): Opened the api configuration for laravel)
=======
>>>>>>> 6f54ca6 (feat(architecture): Set up the backend code base structure)
=======
>>>>>>> 64bc454 (chore(architecture): Opened the api configuration for laravel)
=======
>>>>>>> 6f54ca6 (feat(architecture): Set up the backend code base structure)
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
<<<<<<< HEAD
    })
    ->create();
=======
    })->create();
>>>>>>> 17ff392 (feat(architecture): Set up the backend code base structure)
