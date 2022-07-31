<?php

use LionRoute\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;

/**
 * ------------------------------------------------------------------------------
 * Web Routes
 * ------------------------------------------------------------------------------
 * Here is where you can register web routes for your application
 * ------------------------------------------------------------------------------
 **/

Route::get('/', [HomeController::class, 'index']);

Route::prefix('api', function() {
    Route::prefix('auth', function() {
        Route::post('signin', [LoginController::class, 'auth']);
        Route::post('signout', [LoginController::class, 'auth']);
    });
});