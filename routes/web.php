<?php

declare(strict_types=1);
// -----------------------------------------------------------------------------
// use App\Http\Controllers\ExampleController;
use LionRoute\Route;
use LionRoute\Request;
// -----------------------------------------------------------------------------
Route::addLog();
Route::init();
Request::init(client);
// -----------------------------------------------------------------------------
Route::addMiddleware([]);
// -----------------------------------------------------------------------------
Route::get('/', fn() => info('[index]'));
Route::get('logger', function() {
    logger('test-logger', 'info', ['user' => 'Sleon'], true);
    return success();
});
// Route::get('controller', [ExampleController::class, 'createExample']);
Route::get('route-list', fn() => Route::getFullRoutes());
// -----------------------------------------------------------------------------
Route::dispatch();
