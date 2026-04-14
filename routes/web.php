<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        "APP NAME" => env("APP_NAME"),
        "VERSION" => "1.0",
        "HEALTH" => "OK"
    ]);
});
