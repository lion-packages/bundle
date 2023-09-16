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

\App\Http\Kernel::getInstance()
    ->loadDotEnv(__DIR__ . "/../");

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
    \LionSecurity\RSA::setPath(storage_path(env->RSA_URL_PATH));
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

\App\Http\Kernel::getInstance()
    ->loadCors(require_once(__DIR__ . "/../config/cors.php"));

/**
 * ------------------------------------------------------------------------------
 * Start database service
 * ------------------------------------------------------------------------------
 * Upload data to establish a connection
 * ------------------------------------------------------------------------------
 **/

\App\Http\Kernel::getInstance()
    ->loadConnecions(require_once("../config/database.php"));

/**
 * ------------------------------------------------------------------------------
 * Start email sending service
 * ------------------------------------------------------------------------------
 * enter account access credentials
 * ------------------------------------------------------------------------------
 **/

\App\Http\Kernel::getInstance()
    ->loadAccounts(require_once("../config/email.php"));

/**
 * ------------------------------------------------------------------------------
 * Use rules by routes
 * ------------------------------------------------------------------------------
 * use whatever rules you want to validate input data
 * ------------------------------------------------------------------------------
 **/

\App\Http\Kernel::getInstance()
    ->validateRules(require_once("../routes/rules.php"));

/**
 * ------------------------------------------------------------------------------
 * Web Routes
 * ------------------------------------------------------------------------------
 * Here is where you can register web routes for your application
 * ------------------------------------------------------------------------------
 **/

\App\Http\Kernel::getInstance()
    ->loadRoutes(require_once(__DIR__ . "/../config/middleware.php"), __DIR__ . "/../routes/web.php");
