<?php

use App\Http\Controllers\Api\Auth\AuthenticationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthenticationController::class, 'register'] );

Route::get('/health', function(Request $request){
    return [
        "app_name"=> config('app.name'),
        "version" => '1.0',
        "health" => 'OK',
    ];
});