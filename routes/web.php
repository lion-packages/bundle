<?php

use LionRoute\Route;
use LionRequest\Response;
use Carbon\Carbon;

use App\Http\Middleware\JWT\AuthorizationControlMiddleware;

// || ------------------------------------------------------------------------------
// || Web Routes
// || Here is where you can register web routes for your application.
// || ------------------------------------------------------------------------------

Route::newMiddleware([
    ['jwt-exist', AuthorizationControlMiddleware::class, 'exist'],
    ['jwt-authorize', AuthorizationControlMiddleware::class, 'authorize']
]);

Route::get('/', function() {
    return Response::success('Welcome to index! ' . Carbon::now());
});

Route::prefix('auth', function() {
    Route::get('signin', function() {
        return Response::success('signin...');
    });

    Route::get('signout', function() {
        return Response::success('signout...');
    });
});

Route::prefix('reports', function() {
    Route::middleware(['jwt-exist', 'jwt-authorize'], function() {
        Route::get('word', function() {
            return Response::success('Word report');
        });

        Route::get('excel', function() {
            return Response::success('Excel report');
        });
    });

    Route::get('power-point', function() {
        return Response::success('Power-Point report');
    }, ['jwt-exist', 'jwt-authorize']);

    Route::get('pdf', function() {
        return Response::success('PDF report');
    });
});