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

require_once(__DIR__ . "/../../vendor/autoload.php");

/**
 * ------------------------------------------------------------------------------
 * Register environment variable loader automatically
 * ------------------------------------------------------------------------------
 * .dotenv provides an easy way to access environment variables with $_ENV
 * ------------------------------------------------------------------------------
 **/

(\Dotenv\Dotenv::createImmutable(__DIR__ . "/../../"))->load();

/**
 * ------------------------------------------------------------------------------
 * initialization of predefined constants and functions
 * ------------------------------------------------------------------------------
 **/

include_once(__DIR__ . "/../../config/helpers.php");

/**
 * ------------------------------------------------------------------------------
 * Cross-Origin Resource Sharing (CORS) Configuration
 * ------------------------------------------------------------------------------
 * Here you can configure your settings for cross-origin resource
 * sharing or "CORS". This determines which cross-origin operations
 * can be executed in web browsers.
 * ------------------------------------------------------------------------------
 **/

foreach (require_once(__DIR__ . "/../../config/cors.php") as $key => $header) {
    \LionRequest\Request::header($key, $header);
}

/**
 * ------------------------------------------------------------------------------
 **/

$data = [];
exec(request->command, $data);
finish($data);
