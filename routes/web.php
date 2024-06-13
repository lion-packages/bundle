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

// use App\Exceptions\ExampleException;
use Lion\Route\Route;
use Dotenv\Dotenv;
use Lion\Bundle\Enums\LogTypeEnum;
use Lion\Bundle\Helpers\Commands\Schedule\TaskQueue;
use Lion\Bundle\Helpers\Http\Routes;
use Lion\Database\Driver;
use Lion\Mailer\Mailer;
use Lion\Request\Http;
use Lion\Request\Status;
use Tests\Providers\ExampleProvider;

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
 * Start mail service
 * -----------------------------------------------------------------------------
 * describe connections to establish connecting to multiple databases
 * -----------------------------------------------------------------------------
 **/

Mailer::initialize([
    env('MAIL_NAME', 'lion-app') => [
        'name' => env('MAIL_NAME', 'lion-app'),
        'type' => env('MAIL_TYPE', 'symfony'),
        'host' => env('MAIL_HOST', 'mailhog'),
        'username' => env('MAIL_USER_NAME', 'lion-app'),
        'password' => env('MAIL_PASSWORD', 'lion'),
        'port' => (int) env('MAIL_PORT', 1025),
        'encryption' => env('MAIL_ENCRYPTION', 'tls'),
        'debug' => env('MAIL_DEBUG', false)
    ],
    env('MAIL_NAME_SUPP', 'lion-app') => [
        'name' => env('MAIL_NAME_SUPP', 'lion-app'),
        'type' => env('MAIL_TYPE_SUPP', 'symfony'),
        'host' => env('MAIL_HOST_SUPP', 'mailhog'),
        'username' => env('MAIL_USER_NAME_SUPP', 'lion-app'),
        'password' => env('MAIL_PASSWORD_SUPP', 'lion'),
        'port' => (int) env('MAIL_PORT_SUPP', 1025),
        'encryption' => env('MAIL_ENCRYPTION_SUPP', 'tls'),
        'debug' => env('MAIL_DEBUG_SUPP', false)
    ]
], env('MAIL_NAME', 'lion-app'));

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
Route::get('/', function () {
    TaskQueue::push('send:email:verify', json([
        'email' => 'sleon@dev.com',
        'template' => '<h1>Tasks Test: {{ REPLACE_TEXT }}</h1>'
    ]));

    // TaskQueue::push('example', json([
    //     'key' => 'SERVER_URL',
    // ]));

    // throw new ExampleException('ERR', Status::ROUTE_ERROR, Http::HTTP_BAD_REQUEST);

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
