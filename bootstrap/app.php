<?php

use App\Exceptions\ApiException;
use App\Http\Middleware\EnsureTaskOwnership;
use App\Http\Middleware\IsAuthenticated;
use App\Http\Middleware\Role;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
        $middleware->alias([
            'authentify' => IsAuthenticated::class,
            'role' => Role::class,
            'EnsureTaskOwner' => EnsureTaskOwnership::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request){
            if($request->is('api/*')){
                $response = ApiException::render($e);

                return response()->json(
                    collect($response),
                    $response['status']
                );
            }
        });
    })->create();