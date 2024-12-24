<?php

declare(strict_types=1);

define('LION_START', microtime(true));

define('IS_INDEX', true);

define('DEVELOPMENT_ENVIRONMENT', 'dev' === env('DEVELOPMENT_ENVIRONMENT'));

/**
 * -----------------------------------------------------------------------------
 * Register The Auto Loader
 * -----------------------------------------------------------------------------
 * Composer provides a convenient, automatically generated class loader for
 * this application
 * -----------------------------------------------------------------------------
 **/

require_once(__DIR__ . '/../vendor/autoload.php');

use Lion\Bundle\Helpers\Commands\Schedule\Task;
use Lion\Route\Route;
use Dotenv\Dotenv;
use Lion\Bundle\Enums\LogTypeEnum;
use Lion\Bundle\Helpers\Commands\Schedule\TaskQueue;
use Lion\Bundle\Helpers\Http\Routes;
use Lion\Database\Driver;
use Lion\Files\Store;
use Lion\Mailer\Mailer;
use Tests\Providers\ExampleProvider;

/**
 * -----------------------------------------------------------------------------
 * Register environment variable loader automatically
 * -----------------------------------------------------------------------------
 * .dotenv provides an easy way to access environment variables with $_ENV
 * -----------------------------------------------------------------------------
 **/

if (isSuccess((new Store())->exist(__DIR__ . '/../.env'))) {
    Dotenv::createMutable(__DIR__ . '/../')->load();
}

/**
 * -----------------------------------------------------------------------------
 * Database initialization
 * -----------------------------------------------------------------------------
 * */

Driver::run([
    'default' => env('DB_NAME'),
    'connections' => [
        env('DB_NAME') => [
            'type' => env('DB_TYPE'),
            'host' => env('DB_HOST'),
            'port' => env('DB_PORT'),
            'dbname' => env('DB_NAME'),
            'user' => env('DB_USER'),
            'password' => env('DB_PASSWORD')
        ],
        env('DB_NAME_TEST') => [
            'type' => env('DB_TYPE_TEST'),
            'host' => env('DB_HOST_TEST'),
            'port' => env('DB_PORT_TEST'),
            'dbname' => env('DB_NAME'),
            'user' => env('DB_USER_TEST'),
            'password' => env('DB_PASSWORD_TEST')
        ]
    ]
]);

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
Route::get('/', function (TaskQueue $taskQueue) {
    // $taskQueue->push(new Task(ExampleProvider::class, 'getArrExample', ['name' => 'root']));

    // $taskQueue->push(new Task(ExampleProvider::class, 'myMethod', ['name' => 'root']));

    return info('[index]');
});

Route::get('logger', function () {
    logger('test-logger', LogTypeEnum::INFO, ['user' => 'Sleon'], true);

    return success();
});

Route::prefix('api', function () {
    Route::post('test', [ExampleProvider::class, 'getResult']);
    Route::get('test', fn () => success('test-response'));
    Route::put('test/{id:i}', fn (string $id) => success('test-response: ' . $id));

    Route::middleware(['protect-route-list'], function () {
        Route::delete('test/{id:i}', fn (string $id) => success('test-response: ' . $id));
    });
});

// Route::prefix('users', function () {
//     Route::get('/', function () {
//         return null;
//     });

//     Route::post('/', function () {
//         return null;
//     });
// });

Route::get('route-list', fn () => Route::getFullRoutes(), ['protect-route-list']);
// -----------------------------------------------------------------------------
Route::dispatch();
