<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response;
use LionBundle\Enums\StatusResponseEnum;

/**
 * -----------------------------------------------------------------------------
 * Function to make HTTP requests with guzzlehttp
 * -----------------------------------------------------------------------------
 **/

if (!function_exists('fetch')) {
    function fetch(string $method, string $uri, array $options = []): Response
    {
        return client->request($method, $uri, $options);
    }
}

/**
 * -----------------------------------------------------------------------------
 * Function to get the path of the storage directory
 * -----------------------------------------------------------------------------
 **/

if (!function_exists('storage_path')) {
    function storage_path(string $path = "", bool $index = true): string
    {
        return !$index ? "storage/{$path}" : "../storage/{$path}";
    }
}

/**
 * -----------------------------------------------------------------------------
 * Function to display a response and end the execution of processes
 * -----------------------------------------------------------------------------
 **/

// if (!function_exists('finish')) {
//     function finish(mixed $response = null): void
//     {
//         response->finish($response === null ? success() : $response);
//     }
// }

/**
 * -----------------------------------------------------------------------------
 * Function to display a custom response
 * -----------------------------------------------------------------------------
 **/

if (!function_exists('response')) {
    function response(string $status = 'custom', mixed $response = null, int $code = 200, mixed $data = null): object
    {
        return response->custom($status, $response, $code, $data);
    }
}

/**
 * -----------------------------------------------------------------------------
 * Function to display a success response
 * -----------------------------------------------------------------------------
 **/

if (!function_exists('success')) {
    function success(mixed $response = null, int $code = 200, mixed $data = null): object
    {
        return response->success($response, $code, $data);
    }
}

/**
 * -----------------------------------------------------------------------------
 * Function to display a error response
 * -----------------------------------------------------------------------------
 **/

if (!function_exists('error')) {
    function error(mixed $response = null, int $code = 500, mixed $data = null): object
    {
        return response->error($response, $code, $data);
    }
}

/**
 * -----------------------------------------------------------------------------
 * Function to display a warning response
 * -----------------------------------------------------------------------------
 **/

if (!function_exists('warning')) {
    function warning(mixed $response = null, int $code = 200, mixed $data = null): object
    {
        return response->warning($response, $code, $data);
    }
}

/**
 * -----------------------------------------------------------------------------
 * Function to display a info response
 * -----------------------------------------------------------------------------
 **/

if (!function_exists('info')) {
    function info(mixed $response = null, int $code = 200, mixed $data = null): object
    {
        return response->info($response, $code, $data);
    }
}

/**
 * -----------------------------------------------------------------------------
 * Function to perform a var_dump
 * -----------------------------------------------------------------------------
 **/

if (!function_exists('vd')) {
    function vd(mixed $response): void
    {
        var_dump($response);
    }
}

/**
 * -----------------------------------------------------------------------------
 * Function to add a line to the log file
 * -----------------------------------------------------------------------------
 **/

// if (!function_exists('logger')) {
//     function logger(string $str, string $log_type = 'info', array $data = [], bool $index = true): void
//     {
//         $path = storage_path("logs/", $index);
//         \LionFiles\Store::folder($path);

//         (new \Monolog\Logger('log'))->pushHandler(
//             new \Monolog\Handler\StreamHandler(
//                 ($path . "lion-" . \Carbon\Carbon::now()->format("Y-m-d") . ".log"),
//                 \Monolog\Level::Debug
//             )
//         )->$log_type(json->encode(!isset($_SERVER['REQUEST_URI']) ? $str : [
//             'uri' => $_SERVER['REQUEST_URI'],
//             'data' => json->decode($str)
//         ]), $data);
//     }
// }

/**
 * -----------------------------------------------------------------------------
 * Function to convert data to json
 * -----------------------------------------------------------------------------
 **/

// if (!function_exists('json')) {
//     function json(mixed $value): string
//     {
//         return json->encode($value);
//     }
// }

/**
 * -----------------------------------------------------------------------------
 * Function to check if a response object comes with errors
 * -----------------------------------------------------------------------------
 **/

if (!function_exists('isError')) {
    function isError(object $res): bool
    {
        return in_array($res->status, StatusResponseEnum::errors());
    }
}

/**
 * -----------------------------------------------------------------------------
 * Function to check if a response object is successful
 * -----------------------------------------------------------------------------
 **/

if (!function_exists('isSuccess')) {
    function isSuccess(object $res): bool
    {
        return in_array($res->status, [StatusResponseEnum::SUCCESS->value], true);
    }
}

/**
 * -----------------------------------------------------------------------------
 * Function that returns JWT token if it exists
 * -----------------------------------------------------------------------------
 **/

// if (!function_exists('jwt')) {
//     function jwt(): object
//     {
//         return \LionSecurity\JWT::decode(\LionSecurity\JWT::get());
//     }
// }

/**
 * -----------------------------------------------------------------------------
 * Return function that allows access to the http kernel methods
 * to generate sessions
 * -----------------------------------------------------------------------------
 **/

// if (!function_exists('session')) {
//     function session(): App\Http\Kernel
//     {
//         return App\Http\Kernel::getInstance();
//     }
// }
