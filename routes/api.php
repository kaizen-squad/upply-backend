<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
<<<<<<< HEAD
<<<<<<< HEAD
})->middleware('auth:sanctum');
=======
})->middleware('auth:sanctum');
>>>>>>> 5e55ab6 (chore(architecture): Opened the api configuration for laravel)
=======
})->middleware('auth:sanctum');
>>>>>>> fdddfe8 (fix- Dockerfile key generation)
