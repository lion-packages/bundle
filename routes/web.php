<?php

use LionRoute\Route;
use LionRequest\Response;
use Carbon\Carbon;

use App\Http\Middleware\JWT\AuthorizationMiddleware;

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
    Route::post('signin', function() {
        return Response::success('signin...');
    }, ['no-auth']);

    Route::post('signout', function() {
        return Response::success('signout...');
    }, ['no-auth']);

    Route::get('logout', function() {
        return Response::success('logout...');
    }, ['auth']);
});