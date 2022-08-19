<?php

/**
 * ------------------------------------------------------------------------------
 * Web headers
 * ------------------------------------------------------------------------------
 * This is where you can register headers for your application
 * ------------------------------------------------------------------------------
 **/

date_default_timezone_set($_ENV['SERVER_DEFAULT_TIME_ZONE']);

LionRequest\Request::header('Content-Type', 'application/json; charset=UTF-8');
LionRequest\Request::header('Access-Control-Allow-Origin', env->SERVER_ACCESS_CONTROL_ALLOW_ORIGIN);
LionRequest\Request::header('Access-Control-Max-Age', env->SERVER_ACCESS_CONTROL_MAX_AGE);
LionRequest\Request::header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE');
LionRequest\Request::header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');