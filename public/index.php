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
 * initialization of predefined constants and functions
 * ------------------------------------------------------------------------------
 **/

include_once(__DIR__ . "/../storage/framework/helpers.php");

/**
 * ------------------------------------------------------------------------------
 * Import route for RSA
 * ------------------------------------------------------------------------------
 * Load default route for RSA
 * ------------------------------------------------------------------------------
 **/

if (env->RSA_URL_PATH != '') {
    LionSecurity\RSA::$url_path = storage_path(env->RSA_URL_PATH);
}

/**
 * ------------------------------------------------------------------------------
 * Web headers
 * ------------------------------------------------------------------------------
 * This is where you can register headers for your application
 * ------------------------------------------------------------------------------
 **/

include_once("../routes/header.php");

/**
 * ------------------------------------------------------------------------------
 * Start database service
 * ------------------------------------------------------------------------------
 * Upload data to establish a connection
 * ------------------------------------------------------------------------------
 **/

LionSQL\Drivers\Driver::addLog();

$responseDatabase = LionSQL\Drivers\Driver::run(
    include_once("../config/database.php")
);

if ($responseDatabase->status === 'database-error') {
    finish($responseDatabase);
}

/**
 * ------------------------------------------------------------------------------
 * Start email sending service
 * ------------------------------------------------------------------------------
 * enter account access credentials
 * ------------------------------------------------------------------------------
 **/

LionMailer\Mailer::init([
    'debug' => (int) env->MAIL_DEBUG,
    'host' => env->MAIL_HOST,
    'username' => env->MAIL_USERNAME,
    'password' => env->MAIL_PASSWORD,
    'encryption' => env->MAIL_ENCRYPTION,
    'port' => (int) env->MAIL_PORT,
]);

/**
 * ------------------------------------------------------------------------------
 * Use rules by routes
 * ------------------------------------------------------------------------------
 * use whatever rules you want to validate input data
 * ------------------------------------------------------------------------------
 **/

$rules = include_once("../routes/rules.php");

if (isset($rules[$_SERVER['REQUEST_URI']])) {
    foreach ($rules[$_SERVER['REQUEST_URI']] as $key => $rule) {
        $rule::passes();
        $rule::display();
    }
}

/**
 * ------------------------------------------------------------------------------
 * Web Routes
 * ------------------------------------------------------------------------------
 * Here is where you can register web routes for your application
 * ------------------------------------------------------------------------------
 **/

LionRoute\Route::addLog();
LionRoute\Route::init();
LionRoute\Request::init(client);
include_once("../routes/middleware.php");
include_once("../routes/web.php");
LionRoute\Route::get('route-list', fn() => LionRoute\Route::getRoutes());
LionRoute\Route::dispatch();