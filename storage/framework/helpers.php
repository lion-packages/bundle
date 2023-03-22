<?php

/**
 * ------------------------------------------------------------------------------
 * framework level predefined constants
 * ------------------------------------------------------------------------------
 **/

define('client', new GuzzleHttp\Client());
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
        return json->decode(client->request($method, $uri, $options)->getBody());
    }
}

/**
 * ------------------------------------------------------------------------------
 * Function to make HTTP requests with guzzlehttp
 * ------------------------------------------------------------------------------
 **/

if (!function_exists('fetchXML')) {
    function fetchXML(string $method, string $uri, array $options = []): string {
        return client->request($method, $uri, $options)->getBody()->getContents();
    }
}

/**
 * ------------------------------------------------------------------------------
 * Function to get the path of the storage directory
 * ------------------------------------------------------------------------------
 **/

if (!function_exists('storage_path')) {
    function storage_path(string $path = ""): string {
        return "../storage/{$path}";
    }
}

/**
 * ------------------------------------------------------------------------------
 * Function to display a response and end the execution of processes
 * ------------------------------------------------------------------------------
 **/

if (!function_exists('finish')) {
    function finish(mixed $response): void {
        response->finish($response);
    }
}

/**
 * ------------------------------------------------------------------------------
 * Function to perform a var_dump
 * ------------------------------------------------------------------------------
 **/

if (!function_exists('vd')) {
    function vd(mixed $response): void {
        var_dump($response);
    }
}

/**
 * ------------------------------------------------------------------------------
 * Function to add a line to the log file
 * ------------------------------------------------------------------------------
 **/

if (!function_exists('logger')) {
    function logger(string $str, string $log_type = 'info', array $data = [], bool $index = true): void {
        $file_name = "lion-" . Carbon\Carbon::now()->format("Y-m-d") . ".log";
        $path = !$index ? "storage/logs/" : storage_path("logs/");
        LionFiles\Store::folder($path);

        (new Monolog\Logger('log'))->pushHandler(
            new Monolog\Handler\StreamHandler(
                ($path . $file_name),
                Monolog\Level::Debug
            )
        )->$log_type($str, $data);
    }
}