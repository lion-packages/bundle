<?php

session_start();
define('LION_START', microtime(true));

/**
 * ------------------------------------------------------------------------------
 * Register The Auto Loader
 * ------------------------------------------------------------------------------
 * Composer provides a convenient, automatically generated class loader for
 * this application
 * ------------------------------------------------------------------------------
 **/

require_once(__DIR__ . "/../../vendor/autoload.php");

/**
 * ------------------------------------------------------------------------------
 * Register environment variable loader automatically
 * ------------------------------------------------------------------------------
 * .dotenv provides an easy way to access environment variables with $_ENV
 * ------------------------------------------------------------------------------
 **/

(\Dotenv\Dotenv::createImmutable(__DIR__ . "/../../"))->load();

/**
 * ------------------------------------------------------------------------------
 * initialization of predefined constants and functions
 * ------------------------------------------------------------------------------
 **/

include_once(__DIR__ . "/../../config/helpers.php");

/**
 * ------------------------------------------------------------------------------
 * twig template initialization
 * ------------------------------------------------------------------------------
 **/

echo(
    (new Twig\Environment(new Twig\Loader\FilesystemLoader('app')))
        ->load("index.twig")
        ->render([
            "host" => "http://127.0.0.1:7003",
            "api" => "http://127.0.0.1:8000",
        ])
);
