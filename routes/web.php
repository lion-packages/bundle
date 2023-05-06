<?php

use App\Http\Controllers\Auth\JWTController;
use App\Http\Controllers\Auth\LoginController;
use LionRoute\Route;

/**
 * ------------------------------------------------------------------------------
 * Web Routes
 * ------------------------------------------------------------------------------
 * Here is where you can register web routes for your application
 * ------------------------------------------------------------------------------
 **/

Route::get('/', fn() => info("Welcome to index"));

Route::prefix('api', function() {
    Route::prefix('auth', function() {
        Route::post('login', [LoginController::class, 'auth']);
        Route::post('refresh', [JWTController::class, 'refresh'], ['jwt-authorize']);
    });
});
