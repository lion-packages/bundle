<?php

declare(strict_types=1);

define('LION_START', microtime(true));

define('IS_INDEX', false);

/**
 * -----------------------------------------------------------------------------
 * Register The Auto Loader
 * -----------------------------------------------------------------------------
 * Composer provides a convenient, automatically generated class loader for
 * this application
 * -----------------------------------------------------------------------------
 */

require_once(__DIR__ . '/../vendor/autoload.php');

use Dotenv\Dotenv;
use Lion\Database\Driver;
use Lion\Files\Store;

/**
 * -----------------------------------------------------------------------------
 * Register environment variable loader automatically
 * -----------------------------------------------------------------------------
 * .dotenv provides an easy way to access environment variables with $_ENV
 * -----------------------------------------------------------------------------
 */

if (isSuccess(new Store()->exist(__DIR__ . '/../.env'))) {
    Dotenv::createMutable(__DIR__ . '/../')->load();
}

/**
 * -----------------------------------------------------------------------------
 * Start database service
 * -----------------------------------------------------------------------------
 * describe connections to establish connecting to multiple databases
 * -----------------------------------------------------------------------------
 */

Driver::run([
    'default' => env('DB_DEFAULT'),
    'connections' => [
        env('DB_DEFAULT') => [
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
        env('DB_NAME_TEST_POSTGRESQL') => [
            'type' => env('DB_TYPE_TEST_POSTGRESQL'),
            'host' => env('DB_HOST_TEST_POSTGRESQL'),
            'port' => env('DB_PORT_TEST_POSTGRESQL'),
            'dbname' => env('DB_NAME'),
            'user' => env('DB_USER_TEST_POSTGRESQL'),
            'password' => env('DB_PASSWORD_TEST_POSTGRESQL'),
        ],
    ],
]);
