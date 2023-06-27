<?php

session_start();
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

(\Dotenv\Dotenv::createImmutable(__DIR__ . "/../"))->load();

/**
 * ------------------------------------------------------------------------------
 * initialization of predefined constants and functions
 * ------------------------------------------------------------------------------
 **/

include_once(__DIR__ . "/../config/helpers.php");

/**
 * ------------------------------------------------------------------------------
 * Import route for RSA
 * ------------------------------------------------------------------------------
 * Load default route for RSA
 * ------------------------------------------------------------------------------
 **/

if (env->RSA_URL_PATH != '') {
    \LionSecurity\RSA::$url_path = storage_path(env->RSA_URL_PATH);
}

/**
 * ------------------------------------------------------------------------------
 * Cross-Origin Resource Sharing (CORS) Configuration
 * ------------------------------------------------------------------------------
 * Here you can configure your settings for cross-origin resource
 * sharing or "CORS". This determines which cross-origin operations
 * can be executed in web browsers.
 * ------------------------------------------------------------------------------
 **/

foreach (require_once(__DIR__ . "/../config/cors.php") as $key => $header) {
    \LionRequest\Request::header($key, $header);
}

/**
 * ------------------------------------------------------------------------------
 * Start database service
 * ------------------------------------------------------------------------------
 * Upload data to establish a connection
 * ------------------------------------------------------------------------------
 **/

\LionSQL\Driver::addLog();
$response_database = \LionSQL\Driver::run(
    require_once("../config/database.php")
);

if (isError($response_database)) {
    logger($response_database->message, 'error');
    finish(error(500, $response_database->message));
}

/**
 * ------------------------------------------------------------------------------
 * Start email sending service
 * ------------------------------------------------------------------------------
 * enter account access credentials
 * ------------------------------------------------------------------------------
 **/

$response_email = \LionMailer\MailService::run(
    require_once("../config/email.php")
);

if (isError($response_email)) {
    logger($response_email->message, 'error');
    finish(error(500, $response_email->message));
}

/**
 * ------------------------------------------------------------------------------
 * Use rules by routes
 * ------------------------------------------------------------------------------
 * use whatever rules you want to validate input data
 * ------------------------------------------------------------------------------
 **/

$all_rules = require_once("../routes/rules.php");

foreach ($all_rules[$_SERVER['REQUEST_METHOD']] as $uri => $rules) {
    if (App\Http\Kernel::getInstance()->checkUrl($uri)) {
        foreach ($rules as $key => $rule) {
            $rule::passes();
            $rule::display();
        }
    }
}

/**
 * ------------------------------------------------------------------------------
 * Web Routes
 * ------------------------------------------------------------------------------
 * Here is where you can register web routes for your application
 * ------------------------------------------------------------------------------
 **/

\LionRoute\Route::addLog();
\LionRoute\Route::init();
\LionRoute\Request::init(client);
LionRoute\Route::addMiddleware(require_once("../config/middleware.php"));
include_once("../routes/web.php");
\LionRoute\Route::get('route-list', fn() => \LionRoute\Route::getFullRoutes());
session()->destroy();
\LionRoute\Route::dispatch();
