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

include_once(__DIR__ . "/../storage/framework/functions.php");

/**
 * ------------------------------------------------------------------------------
 * Import route for RSA
 * ------------------------------------------------------------------------------
 * Load default route for RSA
 * ------------------------------------------------------------------------------
 **/

if (env->RSA_URL_PATH != '') {
    LionSecurity\RSA::$url_path = "../" . env->RSA_URL_PATH;
}

/**
 * ------------------------------------------------------------------------------
 * Web headers
 * ------------------------------------------------------------------------------
 * This is where you can register headers for your application
 * ------------------------------------------------------------------------------
 **/

include_once(path("routes\\header.php"));

/**
 * ------------------------------------------------------------------------------
 * Start database service
 * ------------------------------------------------------------------------------
 * Upload data to establish a connection
 * ------------------------------------------------------------------------------
 **/

LionSQL\Drivers\MySQLDriver::init([
    'type' => env->DB_TYPE,
    'host' => env->DB_HOST,
    'port' => env->DB_PORT,
    'dbname' => env->DB_NAME,
    'user' => env->DB_USER,
    'password' => env->DB_PASSWORD
]);

/**
 * ------------------------------------------------------------------------------
 * Start email sending service
 * ------------------------------------------------------------------------------
 * enter account access credentials
 * ------------------------------------------------------------------------------
 **/

LionMailer\Mailer::init([
    'info' => [
        'debug' => (int) env->MAIL_DEBUG,
        'host' => env->MAIL_HOST,
        'port' => (int) env->MAIL_PORT,
        'email' => env->MAIL_EMAIL,
        'password' => env->MAIL_PASSWORD,
        'user_name' => env->MAIL_USER_NAME,
        'encryption' => env->MAIL_ENCRYPTION === 'false' ? false : (env->MAIL_ENCRYPTION === 'true' ? true : false)
    ]
]);

/**
 * ------------------------------------------------------------------------------
 * Initialize validator class language
 * ------------------------------------------------------------------------------
 * valitron provides a set of languages for responses
 * https://github.com/vlucas/valitron/tree/master/lang
 * ------------------------------------------------------------------------------
 **/

Valitron\Validator::lang(env->APP_LANG);

/**
 * ------------------------------------------------------------------------------
 * Use rules by routes
 * ------------------------------------------------------------------------------
 * use whatever rules you want to validate input data
 * ------------------------------------------------------------------------------
 **/

$rules = include_once(path("routes\\rules.php"));

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

LionRoute\Route::init();
LionRoute\Request::init(new GuzzleHttp\Client());
include_once(path("routes\\middleware.php"));
include_once(path("routes\\web.php"));
LionRoute\Route::get('route-list', fn() => LionRoute\Route::getRoutes());
LionRoute\Route::dispatch();