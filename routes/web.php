<?php

use LionRoute\Route;
use LionRequest\Response;
use Carbon\Carbon;

use App\Http\Middleware\JWT\AuthorizationMiddleware;
use App\Http\Controllers\Users\UsersController;

/**
 * ------------------------------------------------------------------------------
 * Web Routes
 * Here is where you can register web routes for your application
 * ------------------------------------------------------------------------------
 **/

Route::newMiddleware([
    ['jwt-exist', AuthorizationMiddleware::class, 'exist'],
    ['jwt-authorize', AuthorizationMiddleware::class, 'authorize'],
    ['jwt-not-authorize', AuthorizationMiddleware::class, 'notAuthorize']
]);

Route::any('/', function() {
    return Response::success('Welcome to index! ' . Carbon::now());
});

Route::prefix('users', function() {
    Route::middleware(['exist'], function() {
        Route::post('create', [UsersController::class, 'createUsers']);
    });

    Route::get('read', [UsersController::class, 'readUsers']);
    Route::get('read/{idusers}', [UsersController::class, 'readUsers']);
});