<?php

define('LION_START', microtime(true));

/**
 * ------------------------------------------------------------------------------
 * Register The Auto Loader
 * ------------------------------------------------------------------------------
 * Composer provides a convenient, automatically generated class loader for
 * this application
 * ------------------------------------------------------------------------------
 **/

require_once(__DIR__ . "/../vendor/autoload.php");

/**
 * ------------------------------------------------------------------------------
 * Register environment variable loader automatically
 * ------------------------------------------------------------------------------
 * .dotenv provides an easy way to access environment variables with $_ENV
 * ------------------------------------------------------------------------------
 **/

(Dotenv\Dotenv::createImmutable(__DIR__ . "/../"))->load();

/**
 * ------------------------------------------------------------------------------
 * Import route for RSA
 * ------------------------------------------------------------------------------
 * Load default route for RSA
 * ------------------------------------------------------------------------------
 **/

if ($_ENV['RSA_URL_PATH'] != '') LionSecurity\RSA::$url_path = "../{$_ENV['RSA_URL_PATH']}";

/**
 * ------------------------------------------------------------------------------
 * Web headers
 * ------------------------------------------------------------------------------
 * This is where you can register headers for your application
 * ------------------------------------------------------------------------------
 **/

include_once(__DIR__ . "/../routes/header.php");

/**
 * ------------------------------------------------------------------------------
 * Start database service
 * ------------------------------------------------------------------------------
 * Upload data to establish a connection
 * ------------------------------------------------------------------------------
 **/

$response_conn = LionSQL\Drivers\MySQLDriver::init([
    'host' => $_ENV['DB_HOST'],
    'port' => $_ENV['DB_PORT'],
    'db_name' => $_ENV['DB_NAME'],
    'user' => $_ENV['DB_USER'],
    'password' => $_ENV['DB_PASSWORD'],
    'charset' => $_ENV['DB_CHARSET']
]);

if ($response_conn->status === 'error') {
    LionRequest\Response::finish(
        LionRequest\Json::encode($response_conn)
    );
}

/**
 * ------------------------------------------------------------------------------
 * Start email sending service
 * ------------------------------------------------------------------------------
 * enter account access credentials
 * ------------------------------------------------------------------------------
 **/

LionMailer\Mailer::init([
    'info' => [
        'debug' => (int) $_ENV['MAIL_DEBUG'],
        'host' => $_ENV['MAIL_HOST'],
        'port' => (int) $_ENV['MAIL_PORT'],
        'email' => $_ENV['MAIL_EMAIL'],
        'password' => $_ENV['MAIL_PASSWORD'],
        'user_name' => $_ENV['MAIL_USER_NAME'],
        'encryption' => $_ENV['MAIL_ENCRYPTION'] === 'false' ? false : ($_ENV['MAIL_ENCRYPTION'] === 'true' ? true : false)
    ]
]);

/**
 * ------------------------------------------------------------------------------
 * Web Routes
 * ------------------------------------------------------------------------------
 * Here is where you can register web routes for your application
 * ------------------------------------------------------------------------------
 **/

LionRoute\Route::init();
include_once(__DIR__ . "/../routes/middleware.php");
include_once(__DIR__ . "/../routes/web.php");
LionRoute\Route::get('route-list', fn() => LionRoute\Route::getRoutes());
LionRoute\Route::dispatch();