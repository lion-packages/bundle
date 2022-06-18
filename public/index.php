<?php

/**
* Lion-PHP - A simple and easy to use PHP framework
*
* @package  Lion-Framework
* @author   Sergio León <sergioleon4004@hotmail.com>
**/

require_once(__DIR__ . "/../vendor/autoload.php");
(Dotenv\Dotenv::createImmutable(__DIR__ . "/../"))->load();
include_once("../routes/header.php");

LionRoute\Route::init();
include_once("../routes/middleware.php");
include_once("../routes/web.php");
LionRoute\Route::dispatch();