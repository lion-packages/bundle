<?php

/**
 * ------------------------------------------------------------------------------
 * Web headers
 * ------------------------------------------------------------------------------
 * This is where you can register headers for your application
 * ------------------------------------------------------------------------------
 **/

header("Access-Control-Allow-Origin: {$_ENV['SERVER_ACCESS_CONTROL_ALLOW_ORIGIN']}");
header("Access-Control-Max-Age: {$_ENV['SERVER_ACCESS_CONTROL_MAX_AGE']}");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Content-Type: application/json; charset=UTF-8");

date_default_timezone_set($_ENV['SERVER_DEFAULT_TIME_ZONE']);