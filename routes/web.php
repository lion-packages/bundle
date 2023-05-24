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

Route::get('/', fn() => info("Welcome to the index, access the web: " . env->SERVER_URL_AUD));

Route::prefix('api', function() {
    Route::prefix('auth', function() {
        Route::post('login', [LoginController::class, 'auth']);
        Route::get('refresh', [SessionController::class, 'refresh'], ['jwt-authorize']);
    });
});
