<?php

/**
 * ------------------------------------------------------------------------------
 * Web headers
 * ------------------------------------------------------------------------------
 * This is where you can register headers for your application
 * ------------------------------------------------------------------------------
 **/

date_default_timezone_set("America/Bogota");

LionRequest\Request::header('Access-Control-Allow-Origin', "*");
LionRequest\Request::header('Content-Type', 'application/json; charset=UTF-8');
LionRequest\Request::header('Access-Control-Max-Age', "3600");
LionRequest\Request::header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE');
LionRequest\Request::header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');