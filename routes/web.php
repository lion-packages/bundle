<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\LionDatabase\UsersController;
use LionRoute\Route;

/**
 * ------------------------------------------------------------------------------
 * Web Routes
 * ------------------------------------------------------------------------------
 * Here is where you can register web routes for your application
 * ------------------------------------------------------------------------------
 **/

Route::any('/', fn() => info('[index]'));

Route::prefix('api', function() {
    Route::post('user-registration', [UsersController::class, 'createUsers']);

    Route::prefix('auth', function() {
        Route::post('login', [LoginController::class, 'auth']);

        Route::middleware(['jwt-without-signature', 'jwt-authorize', 'session'], function() {
            Route::get('refresh', [SessionController::class, 'refresh']);
        });
    });
});
