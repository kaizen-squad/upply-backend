<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
<<<<<<< HEAD
    return response()->json([
        'app_name' => config('app.name'),
        'version' => '1.0',
        'health' => 'OK',
    ]);
=======
    return view('welcome');
>>>>>>> 17ff392 (feat(architecture): Set up the backend code base structure)
});
