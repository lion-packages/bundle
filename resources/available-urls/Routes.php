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
 * Cross-Origin Resource Sharing (CORS) Configuration
 * ------------------------------------------------------------------------------
 * Here you can configure your settings for cross-origin resource
 * sharing or "CORS". This determines which cross-origin operations
 * can be executed in web browsers.
 * ------------------------------------------------------------------------------
 **/

foreach (require_once(__DIR__ . "/../../config/cors.php") as $key => $header) {
    \LionRequest\Request::header($key, $header);
}

/**
 * ------------------------------------------------------------------------------
 **/

$routes = (array) json_decode(request->routes);
array_pop($routes);
$rules = require_once("../../routes/rules.php");
$config_middleware = require_once("../../config/middleware.php");
$rows = [];

foreach ($routes as $route => $methods) {
    $code = uniqid();

    foreach ($methods as $keyMethods => $method) {
        $route_url = str->of("/{$route}")->replace("//", "/")->get();

        if ($method->handler->request != false) {
            $rows[$code]['request'] = [
                'method' => $keyMethods,
                'uri' => $route_url,
                'url' => $method->handler->request->url
            ];
        }

        if ($method->handler->callback != false) {
            $rows[$code]['callback'] = [
                'method' => $keyMethods,
                'uri' => $route_url,
                'function' => "callback",
            ];
        }

        if ($method->handler->controller != false) {
            $rows[$code]['controller'] = [
                'method' => $keyMethods,
                'uri' => $route_url,
                'namespace' => $method->handler->controller->name,
                'function' => $method->handler->controller->function,
            ];
        }

        if (arr->of($method->filters)->length() > 0) {
            foreach ($method->filters as $key => $filter) {
                foreach ($config_middleware as $middlewareClass => $middlewareMethods) {
                    foreach ($middlewareMethods as $middlewareItem => $item) {
                        if ($filter === $item['name']) {
                            $rows[$code]['middlewares'][$middlewareClass][] = [
                                'name' => $filter,
                                // 'namespace' => $middlewareClass,
                                'function' => $item['method'],
                            ];
                        }
                    }
                }
            }
        }

        if (isset($rules[$keyMethods])) {
            if (isset($rules[$keyMethods][$route_url])) {
                foreach ($rules[$keyMethods][$route_url] as $key_uri_rule => $class_rule) {
                    $rows[$code]['params'][] = [
                        'param' => $class_rule::$field,
                        'required' => $class_rule::$disabled === false ? true : false,
                        'namespace' => $class_rule,
                        'function' => "passes"
                    ];
                }
            }
        }
    }
}

finish(success(200, null, [
    'routes' => $rows
]));
