<?php

/**
* Lion-PHP - A simple and easy to use PHP framework
*
* @package  Lion-Framework
* @author   Sergio León <sergioleon4004@hotmail.com>
**/

require_once(__DIR__ . "/../vendor/autoload.php");
(Dotenv\Dotenv::createImmutable(__DIR__ . "/../"))->load();

header("Access-Control-Allow-Origin: {$_ENV['SERVER_ACCESS_CONTROL_ALLOW_ORIGIN']}");
header("Access-Control-Max-Age: {$_ENV['SERVER_ACCESS_CONTROL_MAX_AGE']}");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Content-Type: application/json; charset=UTF-8");
date_default_timezone_set($_ENV['SERVER_DEFAULT_TIME_ZONE']);

include_once("../routes/web.php");