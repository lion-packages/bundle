<?php

use LionRoute\Route;

use App\Http\Middleware\Test\TestMiddleware;
use App\Http\Controllers\HomeController;

// || ------------------------------------------------------------------------------
// || Web Routes
// || Here is where you can register web routes for your application.
// || ------------------------------------------------------------------------------
Route::init([
	'middleware' => [
		Route::newMiddleware('access', TestMiddleware::class, 'access')
	]
]);

Route::any('/', [HomeController::class, 'index']);

Route::prefix('api', function() {
	Route::middleware(['access'], function() {
		Route::post('example', [HomeController::class, 'apiExample']);
	});
});

Route::middleware(['access'], function() {
	Route::post('example', [HomeController::class, 'example']);
});

Route::processOutput(Route::dispatch(3));