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

use Dotenv\Dotenv;
use Lion\Files\Store;

define('IS_INDEX', false);

define('DEVELOPMENT_ENVIRONMENT', 'dev' === env('DEVELOPMENT_ENVIRONMENT'));

/**
 * -----------------------------------------------------------------------------
 * Register environment variable loader automatically
 * -----------------------------------------------------------------------------
 * .dotenv provides an easy way to access environment variables with $_ENV
 * -----------------------------------------------------------------------------
 **/

if (isSuccess((new Store())->exist(__DIR__ . '/../.env'))) {
    Dotenv::createMutable(__DIR__ . '/../')->load();
}
