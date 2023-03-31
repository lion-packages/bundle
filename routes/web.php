<?php

use App\Http\Controllers\Auth\LoginController;
use LionRoute\Route;
use App\Traits\Framework\ClassPath;

/**
 * ------------------------------------------------------------------------------
 * Web Routes
 * ------------------------------------------------------------------------------
 * Here is where you can register web routes for your application
 * ------------------------------------------------------------------------------
 **/

Route::any('/', function() {
    $items = ClassPath::addPostmanJsonItems([], "api/users/create", "POST", true);
    // ClassPath::addPostmanJsonItems("api/users/update", "PUT");
    // ClassPath::addPostmanJsonItems("exito/auth/token", "POST");
    return $items;
    return info("Hola mundo", request);
});

Route::prefix('api', function() {
    Route::prefix('auth', function() {
        Route::post('login', [LoginController::class, 'auth']);
    });
});