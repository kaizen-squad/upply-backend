<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/health', function(Request $request){
    return [
        "app_name"=> config('app.name'),
        "version" => '1.0',
        "health" => 'OK',
    ];
});