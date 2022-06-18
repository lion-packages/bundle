<?php

use LionRoute\Route;
use App\Http\Controllers\Users\UsersController;

/**
 * ------------------------------------------------------------------------------
 * Web Routes
 * Here is where you can register web routes for your application
 * ------------------------------------------------------------------------------
 **/

Route::any('/', function() {
    return LionRequest\Response::success('Welcome to index');
});

Route::prefix('users', function() {
    Route::middleware(['jwt-exist', 'jwt-authorize'], function() {
        Route::post('create', [UsersController::class, 'createUsers']);
    });

    Route::middleware(['jwt-not-exist'], function() {
        Route::get('read', [UsersController::class, 'readUsers']);
        Route::get('read/{idusers}', [UsersController::class, 'readUsers']);
    });
});