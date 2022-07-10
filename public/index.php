<?php

/**
* Lion-PHP - A simple and easy to use PHP framework
*
* @package  Lion-Framework
* @author   Sergio León <sergioleon4004@hotmail.com>
**/

// vendor import
require_once(__DIR__ . "/../vendor/autoload.php");

// .env import
(Dotenv\Dotenv::createImmutable(__DIR__ . "/../"))->load();

// initialize RSA path
if ($_ENV['RSA_URL_PATH'] != '') LionSecurity\RSA::$url_path = "../{$_ENV['RSA_URL_PATH']}";

// all headers import
include_once("../routes/header.php");

// importing and initialize web routes
LionRoute\Route::init();
include_once("../routes/middleware.php");
include_once("../routes/web.php");
LionRoute\Route::get('route-list', function() { return LionRoute\Route::getRoutes(); });
LionRoute\Route::dispatch();