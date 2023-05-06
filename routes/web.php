<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\SessionController;
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
        Route::post('login', [LoginController::class, 'auth'], ['jwt-not-authorize']);
        Route::get('refresh', [SessionController::class, 'refresh'], ['jwt-authorize']);
    });
});
