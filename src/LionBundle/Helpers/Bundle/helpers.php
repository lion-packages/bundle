<?php

declare(strict_types=1);

use Carbon\Carbon;
use Faker\Factory;
use Faker\Generator;
use GuzzleHttp\Psr7\Response;
use Lion\Bundle\Enums\LogTypeEnum;
use Lion\Bundle\Helpers\Env;
use Lion\Bundle\Helpers\Fake;
use Lion\Files\Store;
use Lion\Request\Http;
use Lion\Request\Status;
use Lion\Security\JWT;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

if (!function_exists('now')) {
    /**
     * Get a Carbon instance for the current date and time
     *
     * @param null|DateTimeZone|string $tz
     *
     * @return Carbon
     */
    function now(null|DateTimeZone|string $tz = null): Carbon
    {
        return Carbon::now($tz);
    }
}

if (!function_exists('fetch')) {
    /**
     * Function to make HTTP requests with guzzlehttp
     *
     * @param string $method [HTTP protocol]
     * @param string $uri [URL to make the request]
     * @param array $options [Options to send through the request, such as
     * headers or parameters]
     *
     * @return Response
     */
    function fetch(string $method, string $uri, array $options = []): Response
    {
        return client->request($method, $uri, $options);
    }
}

if (!function_exists('storage_path')) {
    /**
     * Function to get the path of the storage directory
     *
     * @param string $path [directory path]
     * @param bool|boolean $index [Determines whether to run in the root of the
     * project or in './public/index.php']
     *
     * @return string
     */
    function storage_path(string $path = '', bool $index = true): string
    {
        return !$index ? "storage/{$path}" : "../storage/{$path}";
    }
}

if (!function_exists('finish')) {
    /**
     * Function to display a response and end the execution of processes
     *
     * @param mixed|null $response [Message or content of the response]
     *
     * @return void
     */
    function finish(mixed $response = null): void
    {
        response->finish(null === $response ? success() : $response);
    }
}

if (!function_exists('response')) {
    /**
     * Allows you to generate a custom response object
     *
     * @param string $status [The type of status on the object]
     * @param string|null $message [Message inside the object]
     * @param int $code [HTTP status code inside the object]
     * @param mixed|null $data [Extra data inside the object]
     *
     * @return object
     */
    function response(
        string $status = 'custom',
        mixed $message = null,
        int $code = Http::HTTP_OK,
        mixed $data = null
    ): object {
        return response->custom($status, $message, $code, $data);
    }
}

if (!function_exists('success')) {
    /**
     * Allows you to generate an object of type success
     *
     * @param string|null $message [Message inside the object]
     * @param int $code [HTTP status code inside the object]
     * @param mixed|null $data [Extra data inside the object]
     *
     * @return object
     */
    function success(mixed $message = null, int $code = Http::HTTP_OK, mixed $data = null): object
    {
        return response->success($message, $code, $data);
    }
}

if (!function_exists('error')) {
    /**
     * Allows you to generate an object of type error
     *
     * @param string|null $message [Message inside the object]
     * @param int $code [HTTP status code inside the object]
     * @param mixed|null $data [Extra data inside the object]
     *
     * @return object
     */
    function error(mixed $message = null, int $code = Http::HTTP_INTERNAL_SERVER_ERROR, mixed $data = null): object
    {
        return response->error($message, $code, $data);
    }
}

if (!function_exists('warning')) {
    /**
     * Allows you to generate an object of type warning
     *
     * @param string|null $message [Message inside the object]
     * @param int $code [HTTP status code inside the object]
     * @param mixed|null $data [Extra data inside the object]
     *
     * @return object
     */
    function warning(mixed $message = null, int $code = Http::HTTP_OK, mixed $data = null): object
    {
        return response->warning($message, $code, $data);
    }
}

if (!function_exists('info')) {
    /**
     * Allows you to generate an object of type info
     *
     * @param string|null $message [Message inside the object]
     * @param int $code [HTTP status code inside the object]
     * @param mixed|null $data [Extra data inside the object]
     *
     * @return object
     */
    function info(mixed $message = null, int $code = Http::HTTP_OK, mixed $data = null): object
    {
        return response->info($message, $code, $data);
    }
}

if (!function_exists('vd')) {
    /**
     * Function to perform a var_dump
     *
     * @param mixed $response [Message or content to view]
     *
     * @return void
     */
    function vd(mixed $response): void
    {
        var_dump($response);
    }
}

if (!function_exists('logger')) {
    /**
     * Function to add a line to the log file
     *
     * @param string $message [Message or content to add to the log record]
     * @param string $logType [Log type]
     * @param array $data [data to add to the log record]
     * @param bool|boolean $index [Determines whether you are in the root of
     * the project or in './public/index.php']
     *
     * @return void
     */
    function logger(
        string $message,
        LogTypeEnum $logType = LogTypeEnum::INFO,
        array $data = [],
        bool $index = true
    ): void {
        $logTypeValue = $logType->value;

        $path = storage_path('logs/monolog/', $index);

        $fileName = "{$path}lion-" . Carbon::now()->format('Y-m-d') . '.log';

        (new Store())->folder($path);

        $logger = new Logger('log');

        $logger->pushHandler(new StreamHandler($fileName, Level::Info));

        if (!isset($_SERVER['REQUEST_URI'])) {
            $logger->$logTypeValue(json_encode($message), $data);
        } else {
            $logger->$logTypeValue(
                json_encode([
                    'uri' => $_SERVER['REQUEST_URI'],
                    'data' => json_decode($message)
                ]),
                $data
            );
        }
    }
}

if (!function_exists('json')) {
    /**
     * Function to convert data to json
     *
     * @param mixed $values [Values to convert to JSON]
     *
     * @return string
     */
    function json(mixed $values): string
    {
        return json_encode($values);
    }
}

if (!function_exists('isError')) {
    /**
     * Function to check if a response object comes with errors
     *
     * @param array|object $response [Response object]
     *
     * @return bool
     */
    function isError(array|object $response): bool
    {
        $response = (object) $response;

        if (empty($response->status)) {
            return false;
        }

        return in_array($response->status, response->getErrors());
    }
}

if (!function_exists('isSuccess')) {
    /**
     * Function to check if a response object is successful
     *
     * @param array|object $response [Response object]
     *
     * @return bool
     */
    function isSuccess(array|object $response): bool
    {
        $response = (object) $response;

        if (empty($response->status)) {
            return false;
        }

        return in_array($response->status, [Status::SUCCESS], true);
    }
}

if (!function_exists('jwt')) {
    /**
     * Gets the HTTP_AUTHORIZATION header token
     *
     * @return string|bool
     */
    function jwt(): string|bool
    {
        return (new JWT())->getJWT();
    }
}

if (!function_exists('fake')) {
    /**
     * Function that generates a Generator object to obtain fake data
     *
     * @param string $locale [Regional configuration]
     *
     * @return Generator
     */
    function fake(string $locale = Factory::DEFAULT_LOCALE): Generator
    {
        return Fake::get($locale);
    }
}

if (!function_exists('env')) {
    /**
     * Gets the value defined for an environment variable
     *
     * @param string $key [Property name]
     * @param mixed $default [Default value]
     *
     * @return mixed
     */
    function env(string $key, mixed $default = null): mixed
    {
        return Env::get($key, $default);
    }
}
