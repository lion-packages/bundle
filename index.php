<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Allow: GET, POST, PUT, DELETE");
header("Content-Type: application/json; charset=UTF-8");
session_start();

require_once("vendor/autoload.php");
spl_autoload_register(function($class_name) {
    require_once(str_replace("\\", "/", $class_name) . '.php');
});
(Dotenv\Dotenv::createImmutable(__DIR__))->load();
include_once("routes/web.php");