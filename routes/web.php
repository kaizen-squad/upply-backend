<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'app_name' => config('app.name'),
        'version' => '1.0',
        'health' => 'OK',
    ]);
});
