<?php

require_once("vendor/autoload.php");
spl_autoload_register(function($class_name) {
    require_once(str_replace("\\", "/", $class_name) . '.php');
});

(Dotenv\Dotenv::createImmutable(__DIR__))->load();

header("Access-Control-Allow-Origin: *");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Allow: GET, POST, PUT, DELETE, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");
date_default_timezone_set($_ENV['SERVER_DEFAULT_TIME_ZONE']);

include_once("routes/web.php");