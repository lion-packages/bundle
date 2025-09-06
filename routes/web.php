<?php

declare(strict_types=1);

define('LION_START', microtime(true));

define('IS_INDEX', true);

/**
 * -----------------------------------------------------------------------------
 * Register The Auto Loader
 * -----------------------------------------------------------------------------
 * Composer provides a convenient, automatically generated class loader for
 * this application
 * -----------------------------------------------------------------------------
 **/

require_once(__DIR__ . '/../vendor/autoload.php');

use Dotenv\Dotenv;
use Lion\Bundle\Enums\LogTypeEnum;
use Lion\Bundle\Helpers\Commands\Queue\TaskQueue;
use Lion\Database\Driver;
use Lion\Files\Store;
use Lion\Route\Route;
use Tests\Providers\ExampleProvider;
use Lion\Bundle\Support\Http\Routes;

/**
 * -----------------------------------------------------------------------------
 * Register environment variable loader automatically
 * -----------------------------------------------------------------------------
 * .dotenv provides an easy way to access environment variables with $_ENV
 * -----------------------------------------------------------------------------
 **/

if (isSuccess(new Store()->exist(__DIR__ . '/../.env'))) {
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
            'password' => env('DB_PASSWORD'),
        ],
        env('DB_NAME_TEST') => [
            'type' => env('DB_TYPE_TEST'),
            'host' => env('DB_HOST_TEST'),
            'port' => env('DB_PORT_TEST'),
            'dbname' => env('DB_NAME'),
            'user' => env('DB_USER_TEST'),
            'password' => env('DB_PASSWORD_TEST'),
        ],
    ],
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
Route::get('/', function (): stdClass {
    /** @phpstan-ignore-next-line */
    $taskQueue = new TaskQueue([
        'scheme' => env('REDIS_SCHEME'),
        'host' => env('REDIS_HOST'),
        'port' => env('REDIS_PORT'),
        'parameters' => [
            'password' => env('REDIS_PASSWORD'),
            'database' => TaskQueue::LION_DATABASE,
        ],
    ]);

    $taskQueue
        ->push(
            new \Lion\Bundle\Support\Task(ExampleProvider::class, 'getArrExample', [
                'name' => 'root',
            ]),
            new \Lion\Bundle\Support\Task(ExampleProvider::class, 'getResult')
        );

    return info('[index]');
});

Route::get('logger', function () {
    logger('test-logger', LogTypeEnum::INFO, [
        'user' => 'Sleon',
    ]);

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
