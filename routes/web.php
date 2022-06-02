<?php

use LionRoute\Route;
use LionRequest\Response;
use Carbon\Carbon;

use App\Http\Middleware\JWT\AuthorizationMiddleware;
use App\Http\Controllers\Auth\{ LoginController, RegisterController };

/**
 * ------------------------------------------------------------------------------
 * Web Routes
 * Here is where you can register web routes for your application
 * ------------------------------------------------------------------------------
 **/

Route::newMiddleware([
    ['auth', AuthorizationMiddleware::class, 'authorize'],
    ['no-auth', AuthorizationMiddleware::class, 'notAuthorize']
]);

Route::get('/', function() {
    return Response::success('Welcome to index! ' . Carbon::now());
});

Route::prefix('auth', function() {
    Route::post('signin', [LoginController::class, 'auth'], ['no-auth']);
    Route::get('logout', [LoginController::class, 'logout'], ['auth']);
    Route::post('signout', [RegisterController::class, 'register'], ['no-auth']);
});