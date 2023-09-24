<?php

/**
 * ------------------------------------------------------------------------------
 * Cross-Origin Resource Sharing (CORS) Configuration
 * ------------------------------------------------------------------------------
 * Here you can configure your settings for cross-origin resource
 * sharing or "CORS". This determines which cross-origin operations
 * can be executed in web browsers.
 * ------------------------------------------------------------------------------
 **/

date_default_timezone_set(env->SERVER_DATE_TIMEZONE);

return [
    'Access-Control-Allow-Origin' => '*',
    'Content-Type' => 'application/json; charset=UTF-8',
    'Access-Control-Max-Age' => '0',
    'Allow' => 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
    'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
    'Access-Control-Allow-Headers' => 'Origin, X-Requested-With, Content-Type, Accept, Authorization'
];
