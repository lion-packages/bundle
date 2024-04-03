<?php

declare(strict_types=1);

define('LION_START', microtime(true));

/**
 * -----------------------------------------------------------------------------
 * Register The Auto Loader
 * -----------------------------------------------------------------------------
 * Composer provides a convenient, automatically generated class loader for
 * this application
 * -----------------------------------------------------------------------------
 **/

require_once(__DIR__ . '/../vendor/autoload.php');

use Lion\Route\Route;
use Dotenv\Dotenv;
use Lion\Bundle\Helpers\Http\Routes;

/**
 * -----------------------------------------------------------------------------
 * Register environment variable loader automatically
 * -----------------------------------------------------------------------------
 * .dotenv provides an easy way to access environment variables with $_ENV
 * -----------------------------------------------------------------------------
 **/

Dotenv::createImmutable(__DIR__ . '/../')->load();

/**
 * -----------------------------------------------------------------------------
 * Web Routes
 * -----------------------------------------------------------------------------
 * Here is where you can register web routes for your application
 * -----------------------------------------------------------------------------
 **/

Route::init();
Route::addMiddleware(Routes::getMiddleware());
// -----------------------------------------------------------------------------
Route::get('/', fn () => info('[index]'));

Route::get('logger', function () {
    logger('test-logger', 'info', ['user' => 'Sleon'], true);

    return success();
});

Route::prefix('api', function () {
    Route::post('test', fn () => ['token' => jwt()]);
    Route::get('test', fn () => success('test-response'));
    Route::put('test/{id:i}', fn (string $id) => success('test-response: ' . $id));

    Route::middleware(['protect-route-list'], function () {
        Route::delete('test/{id:i}', fn (string $id) => success('test-response: ' . $id));
    });
});

Route::get('route-list', fn () => Route::getFullRoutes(), ['protect-route-list']);
// -----------------------------------------------------------------------------
Route::dispatch();
