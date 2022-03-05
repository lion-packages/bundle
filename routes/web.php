<?php

use LionRoute\Route;
use LionRoute\Request;

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

$_SESSION['user_session'] = isset($_SESSION['user_session']) ? $_SESSION['user_session'] : false;

Route::any('/', function() {
	Route::processOutput(
		new Request('warning', 'Page not found. [index]')
	);
});

Route::middleware(['before' => 'no-auth'], function() {
    Route::prefix('autenticar', function() {
        Route::post('ingreso', [ExampleController::class, 'methodExample']);
    });
});

Route::processOutput(Route::dispatch(3)); 