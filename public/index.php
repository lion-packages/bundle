<?php

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

// all headers import
include_once("../routes/header.php");

// importing and initialize web routes
LionRoute\Route::init();
include_once("../routes/middleware.php");
include_once("../routes/web.php");
LionRoute\Route::get('route-list', function() { return LionRoute\Route::getRoutes(); });
LionRoute\Route::dispatch();