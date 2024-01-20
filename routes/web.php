<?php

declare(strict_types=1);

use Lion\Route\Route;
use Tests\Providers\ExampleProvider;


Route::init();
Route::addMiddleware([]);
// -----------------------------------------------------------------------------
Route::get('/', fn() => info('[index]'));

Route::get('logger', function() {
    logger('test-logger', 'info', ['user' => 'Sleon'], true);

    return success();
});

Route::get('inject', [ExampleProvider::class, 'getArrExample']);
// Route::get('controller', [ExampleController::class, 'createExample']);

Route::get('route-list', fn() => Route::getFullRoutes());
// -----------------------------------------------------------------------------
Route::dispatch();
