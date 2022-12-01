<?php

/**
 * ------------------------------------------------------------------------------
 * framework level predefined constants
 * ------------------------------------------------------------------------------
 **/

define('request', LionRequest\Request::getInstance()->capture());
define('response', LionRequest\Response::getInstance());
define('json', LionRequest\Json::getInstance());
define('env', (object) $_ENV);

/**
 * ------------------------------------------------------------------------------
 * Function to make HTTP requests with guzzlehttp
 * ------------------------------------------------------------------------------
 **/

if (!function_exists('fetch')) {
    function fetch(string $method, string $uri, array $options = []): array {
        return json->decode(
            (new GuzzleHttp\Client())
                ->request($method, $uri, $options)
                ->getBody()
        );
    }
}

/**
 * ------------------------------------------------------------------------------
 * Function to get current directory path
 * ------------------------------------------------------------------------------
 **/

if (!function_exists('path')) {
    function path(string $path = ""): string {
        return __DIR__ . ".\\..\\..\\{$path}";
    }
}

/**
 * ------------------------------------------------------------------------------
 * Function to get the path of the storage directory
 * ------------------------------------------------------------------------------
 **/

if (!function_exists('storage_path')) {
    function storage_path(string $path = ""): string {
        return path("storage\\{$path}");
    }
}

/**
 * ------------------------------------------------------------------------------
 * Function to get the path of the public directory
 * ------------------------------------------------------------------------------
 **/

if (!function_exists('storage_public')) {
    function public_path(string $path = ""): string {
        return path("public\\{$path}");
    }
}