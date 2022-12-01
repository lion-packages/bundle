<?php

use LionRoute\{ Route, Request };
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;

/**
 * ------------------------------------------------------------------------------
 * Web Routes
 * ------------------------------------------------------------------------------
 * Here is where you can register web routes for your application
 * ------------------------------------------------------------------------------
 **/

Route::any('/', [HomeController::class, 'index']);

Route::prefix('api', function() {
    Route::prefix('auth', function() {
        Route::post('login', [LoginController::class, 'auth']);
    });
});