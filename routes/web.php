<?php

use LionRoute\Route;
use LionRoute\Request;

use App\Http\Controllers\Controller;
use App\Http\Middleware\UserAuth;
use App\Http\Controllers\Example\ExampleController;

Route::init([
    'class' => [
        'RouteCollector' => Phroute\Phroute\RouteCollector::class,
        'Dispatcher' => Phroute\Phroute\Dispatcher::class
    ],
    'middleware' => [
    	Route::newMiddleware('auth', UserAuth::class, 'auth'),
    	Route::newMiddleware('no-auth', UserAuth::class, 'noAuth')
    ]
]);

Route::any('/', function() {
	return new Request('warning', 'Page not found. [index]');
});

Route::middleware(['before' => 'no-auth'], function() {
    Route::prefix('autenticar', function() {
        Route::post('ingreso', [ExampleController::class, 'methodExample']);
    });
});

Route::processOutput(Route::dispatch(3)); 