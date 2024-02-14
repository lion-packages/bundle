<?php

declare(strict_types=1);

use Lion\Route\Route;

Route::init();
Route::addMiddleware([]);
// -----------------------------------------------------------------------------
Route::get('/', fn() => info('[index]'));

Route::get('logger', function() {
    logger('test-logger', 'info', ['user' => 'Sleon'], true);

    return success();
});

Route::prefix('api', function() {
    Route::post('test', fn() => success('test-response'));
    Route::get('test', fn() => success('test-response'));
    Route::put('test/{id:i}', fn(string $id) => success('test-response: ' . $id));
    Route::delete('test/{id:i}', fn(string $id) => success('test-response: ' . $id));
});

Route::get('route-list', fn() => Route::getFullRoutes());
// -----------------------------------------------------------------------------
Route::dispatch();
