<?php

declare(strict_types=1);

use Carbon\Carbon;
use Faker\Factory;
use Faker\Generator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Lion\Bundle\Enums\LogTypeEnum;
use Lion\Bundle\Helpers\Env;
use Lion\Bundle\Helpers\Fake;
use Lion\Bundle\Support\Http\Fetch;
use Lion\Files\Store;
use Lion\Request\Http;
use Lion\Request\Request;
use Lion\Request\Status;
use Lion\Security\JWT;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;

if (!function_exists('request')) {
    /**
     * Object with properties captured in an HTTP request and you can add
     * properties to the object
     *
     * @param string $key [Property name]
     *
     * @return mixed
     */
    function request(string $key = ''): mixed
    {
        $data = new Request()->capture();

        if (!empty($key)) {
            return $data->$key ?? null;
        }

        return $data;
    }
}

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
     * Function to make HTTP requests with GuzzleHttp
     *
     * @param Fetch $fetch [Defines parameters for consuming HTTP requests with
     * GuzzleHttp]
     *
     * @return ResponseInterface
     *
     * @throws GuzzleException
     */
    function fetch(Fetch $fetch): ResponseInterface
    {
        $fetchConfiguration = [];

        if (null != $fetch->getFetchConfiguration()) {
            $fetchConfiguration = $fetch
                ->getFetchConfiguration()
                ->getConfiguration();
        }

        $client = new Client($fetchConfiguration);

        return $client->request($fetch->getHttpMethod(), $fetch->getUri(), $fetch->getOptions());
    }
}

if (!function_exists('storage_path')) {
    /**
     * Function to get the path of the storage directory
     *
     * @param string $path [Directory path]
     *
     * @return string
     */
    function storage_path(string $path): string
    {
        /** @phpstan-ignore-next-line */
        return !IS_INDEX ? "storage/{$path}" : "../storage/{$path}";
    }
}

if (!function_exists('public_path')) {
    /**
     * Function to get the path of the public directory
     *
     * @param string $path [Directory path]
     *
     * @return string
     */
    function public_path(string $path): string
    {
        /** @phpstan-ignore-next-line */
        return !IS_INDEX ? "public/{$path}" : $path;
    }
}

if (!function_exists('finish')) {
    /**
     * Function to display a response and end the execution of processes
     *
     * @param mixed|null $response [Message or content of the response]
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    function finish(mixed $response = null): void
    {
        RESPONSE->finish(null === $response ? success() : $response);
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
     * @return stdClass
     */
    function response(
        string $status = 'custom',
        ?string $message = null,
        int $code = Http::OK,
        mixed $data = null
    ): stdClass {
        return RESPONSE->custom($status, $message, $code, $data);
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
     * @return stdClass
     */
    function success(?string $message = null, int $code = Http::OK, mixed $data = null): stdClass
    {
        return RESPONSE->success($message, $code, $data);
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
     * @return stdClass
     */
    function error(?string $message = null, int $code = Http::INTERNAL_SERVER_ERROR, mixed $data = null): stdClass
    {
        return RESPONSE->error($message, $code, $data);
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
     * @return stdClass
     */
    function warning(?string $message = null, int $code = Http::OK, mixed $data = null): stdClass
    {
        return RESPONSE->warning($message, $code, $data);
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
     * @return stdClass
     */
    function info(?string $message = null, int $code = Http::OK, mixed $data = null): stdClass
    {
        return RESPONSE->info($message, $code, $data);
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
     * @param LogTypeEnum $logType [Log type]
     * @param array<string, mixed> $data [data to add to the log record]
     *
     * @return void
     */
    function logger(
        string $message,
        LogTypeEnum $logType = LogTypeEnum::INFO,
        array $data = []
    ): void {
        $logTypeValue = $logType->value;

        $path = storage_path('logs/monolog/');

        $fileName = "{$path}lion-" . now()->format('Y-m-d') . '.log';

        new Store()->folder($path);

        $logger = new Logger('log');

        $streamHandler = new StreamHandler($fileName, Level::Info);

        $logger->pushHandler($streamHandler);

        if (!isset($_SERVER['REQUEST_URI'])) {
            /** @var non-empty-string $json */
            $json = json_encode($message);
        } else {
            /** @var non-empty-string $json */
            $json = json_encode([
                'uri' => $_SERVER['REQUEST_URI'],
                'data' => json_decode($message),
            ]);
        }

        $logger->$logTypeValue($json, $data);
    }
}

if (!function_exists('json')) {
    /**
     * Function to convert data to json
     *
     * @param mixed $values [Values to convert to JSON]
     *
     * @return string
     *
     * @throws JsonException [If JSON format encoding fails]
     */
    function json(mixed $values): string
    {
        $json = json_encode($values);

        if (!$json) {
            throw new JsonException(json_last_error_msg(), 500);
        }

        return $json;
    }
}

if (!function_exists('isError')) {
    /**
     * Function to check if a response object comes with errors
     *
     * @param mixed $response [Response object]
     *
     * @return bool
     */
    function isError(mixed $response): bool
    {
        if (is_array($response)) {
            $response = (object) $response;
        }

        if (!$response instanceof stdClass) {
            return false;
        }

        if (empty($response->status)) {
            return false;
        }

        return in_array($response->status, RESPONSE->getErrors());
    }
}

if (!function_exists('isSuccess')) {
    /**
     * Function to check if a response object is successful
     *
     * @param mixed $response [Response object]
     *
     * @return bool
     */
    function isSuccess(mixed $response): bool
    {
        if (is_array($response)) {
            $response = (object) $response;
        }

        if (!$response instanceof stdClass) {
            return false;
        }

        if (empty($response->status)) {
            return false;
        }

        return Status::SUCCESS === $response->status;
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
        return new JWT()->getJWT();
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
