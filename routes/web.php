<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use LionRoute\Route;

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

    Route::get('jsonplaceholder', 'https://jsonplaceholder.typicode.com/posts');
});