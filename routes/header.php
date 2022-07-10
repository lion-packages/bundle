<?php

/**
 * ------------------------------------------------------------------------------
 * Web headers
 * ------------------------------------------------------------------------------
 * This is where you can register headers for your application
 * ------------------------------------------------------------------------------
 **/

date_default_timezone_set($_ENV['SERVER_DEFAULT_TIME_ZONE']);

LionRequest\Request::header([
    ['type' => "Content-Type", 'value' => "application/json; charset=UTF-8"],
    ['type' => "Access-Control-Allow-Origin", 'value' => $_ENV['SERVER_ACCESS_CONTROL_ALLOW_ORIGIN']],
    ['type' => "Access-Control-Max-Age", 'value' => $_ENV['SERVER_ACCESS_CONTROL_MAX_AGE']],
    ['type' => "Access-Control-Allow-Methods", 'value' => "GET, POST, PUT, DELETE"],
    ['type' => "Access-Control-Allow-Headers", 'value' => "Origin, X-Requested-With, Content-Type, Accept, Authorization"]
]);