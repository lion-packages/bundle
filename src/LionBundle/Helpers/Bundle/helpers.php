<?php

declare(strict_types=1);

use Carbon\Carbon;
use Carbon\Month;
use Carbon\WeekDay;
use Faker\Factory;
use Faker\Generator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Lion\Bundle\Enums\LogTypeEnum;
use Lion\Bundle\Helpers\Env;
use Lion\Bundle\Helpers\Fake;
use Lion\Bundle\Support\Http\Fetch;
use Lion\Database\Connection;
use Lion\Files\Store;
use Lion\Request\Http;
use Lion\Request\Request;
use Lion\Request\Status;
use Lion\Security\JWT;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;

if (!function_exists('getDefaultConnection')) {
    /**
     * Returns the default database connection name.
     *
     * <code>
     *     $default = getDefaultConnection(); // uses Connection::getDefaultConnectionName()
     * </code>
     *
     * @return string The default connection name.
     */
    function getDefaultConnection(): string
    {
        return Connection::getDefaultConnectionName();
    }
}

if (!function_exists('request')) {
    /**
     * Retrieves the HTTP request object or a specific property from it.
     *
     * <code>
     *     $data = request(); // Get all the data
     *     $name = request('name');
     * </code>
     *
     * @param string $key The property name to retrieve (optional).
     *
     * @return mixed The full request object or the property value if provided.
     */
    function request(string $key = ''): mixed
    {
        $data = new Request()->capture();

        if ($key !== '') {
            return $data->$key ?? null;
        }

        return $data;
    }
}

if (!function_exists('now')) {
    /**
     * Returns a Carbon instance for the current date and time.
     *
     * <code>
     *     $date = now()->format('Y-m-d H:i:s');
     * </code>
     *
     * @param DateTimeZone|string|int|null $tz The timezone to apply, if any.
     *
     * @return Carbon The current date and time as a Carbon instance.
     */
    function now(DateTimeZone|string|int|null $tz = null): Carbon
    {
        return Carbon::now($tz);
    }
}

if (!function_exists('parse')) {
    /**
     * Creates a Carbon instance from a given value.
     *
     * @param DateTimeInterface|WeekDay|Month|string|int|float|null $time The
     * date/time value to parse.
     * @param DateTimeZone|string|int|null $timezone The timezone to apply, if any.
     *
     * @return Carbon The resulting Carbon instance.
     */
    function parse(
        DateTimeInterface|WeekDay|Month|string|int|float|null $time,
        DateTimeZone|string|int|null $timezone = null
    ): Carbon {
        return Carbon::parse($time, $timezone);
    }
}

if (!function_exists('fetch')) {
    /**
     * Sends an HTTP request using GuzzleHttp.
     *
     * @param Fetch $fetch Defines parameters for consuming HTTP requests with
     * GuzzleHttp.
     *
     * @return ResponseInterface The HTTP response object.
     *
     * @throws GuzzleException If the request fails.
     */
    function fetch(Fetch $fetch): ResponseInterface
    {
        $fetchConfiguration = [];

        if (null !== $fetch->getFetchConfiguration()) {
            $fetchConfiguration = $fetch
                ->getFetchConfiguration()
                ->getConfiguration();
        }

        $client = new Client($fetchConfiguration);

        return $client->request(
            $fetch->getHttpMethod(),
            $fetch->getUri(),
            $fetch->getOptions()
        );
    }
}

if (!function_exists('storage_path')) {
    /**
     * Gets the absolute path to the storage directory.
     *
     * @param non-empty-string $path Relative directory path within storage.
     *
     * @return non-empty-string The resolved storage path.
     */
    function storage_path(string $path): string
    {
        /** @phpstan-ignore-next-line */
        return !IS_INDEX ? "storage/{$path}" : "../storage/{$path}";
    }
}

if (!function_exists('public_path')) {
    /**
     * Gets the absolute path to the public directory.
     *
     * @param non-empty-string $path Relative directory path within public.
     *
     * @return non-empty-string The resolved public path.
     */
    function public_path(string $path): string
    {
        /** @phpstan-ignore-next-line */
        return !IS_INDEX ? "public/{$path}" : $path;
    }
}

if (!function_exists('finish')) {
    /**
     * Displays a response and terminates further execution.
     *
     * @param string|int|float|bool|array<object>|object|null $response Message or content of the response.
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    function finish(string|int|float|bool|array|object|null $response = null): void
    {
        RESPONSE->finish(null === $response ? success() : $response);
    }
}

if (!function_exists('response')) {
    /**
     * Generates a custom response object.
     *
     * @param string $status The type of status for the response.
     * @param string|null $message The message to include in the response.
     * @param int $code The HTTP status code of the response.
     * @param string|int|float|bool|array<object>|object|null $data Extra data to
     * include in the response.
     *
     * @return stdClass The generated response object.
     */
    function response(
        string $status = 'custom',
        ?string $message = null,
        int $code = Http::OK,
        string|int|float|bool|array|object|null $data = null
    ): stdClass {
        return RESPONSE->custom($status, $message, $code, $data);
    }
}

if (!function_exists('success')) {
    /**
     * Generates a success response object.
     *
     * @param string|null $message The message to include in the response.
     * @param int $code The HTTP status code of the response.
     * @param string|int|float|bool|array<object>|object|null $data Extra data to
     * include in the response.
     *
     * @return stdClass The generated success response object.
     */
    function success(
        ?string $message = null,
        int $code = Http::OK,
        string|int|float|bool|array|object|null $data = null
    ): stdClass {
        return RESPONSE->success($message, $code, $data);
    }
}

if (!function_exists('error')) {
    /**
     * Generates an error response object.
     *
     * @param string|null $message The message to include in the response.
     * @param int $code The HTTP status code of the response.
     * @param string|int|float|bool|array<object>|object|null $data Extra data to
     * include in the response.
     *
     * @return stdClass The generated error response object.
     */
    function error(
        ?string $message = null,
        int $code = Http::INTERNAL_SERVER_ERROR,
        string|int|float|bool|array|object|null $data = null
    ): stdClass {
        return RESPONSE->error($message, $code, $data);
    }
}

if (!function_exists('warning')) {
    /**
     * Generates a warning response object.
     *
     * @param string|null $message The message to include in the response.
     * @param int $code The HTTP status code of the response.
     * @param string|int|float|bool|array<object>|object|null $data Extra data to
     * include in the response.
     *
     * @return stdClass The generated warning response object.
     */
    function warning(
        ?string $message = null,
        int $code = Http::OK,
        string|int|float|bool|array|object|null $data = null
    ): stdClass {
        return RESPONSE->warning($message, $code, $data);
    }
}

if (!function_exists('info')) {
    /**
     * Generates an info response object.
     *
     * @param string|null $message The message to include in the response.
     * @param int $code The HTTP status code of the response.
     * @param string|int|float|bool|array<object>|object|null $data Extra data to
     * include in the response.
     *
     * @return stdClass The generated info response object.
     */
    function info(
        ?string $message = null,
        int $code = Http::OK,
        string|int|float|bool|array|object|null $data = null
    ): stdClass {
        return RESPONSE->info($message, $code, $data);
    }
}

if (!function_exists('vd')) {
    /**
     * Dumps the given value as a pretty-printed JSON string.
     *
     * @param mixed $response The value to dump.
     *
     * @return void
     *
     * @codeCoverageIgnore
     */
    function vd(mixed $response): void
    {
        try {
            echo json_encode(
                $response,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
            ) . PHP_EOL;
        } catch (\JsonException $e) {
            var_dump("ENTRA");
            var_dump($response); // fallback if encoding fails
        }
    }
}

if (!function_exists('logger')) {
    /**
     * Writes a log entry to the application log file.
     *
     * @param string $message The message or content to add to the log record.
     * @param LogTypeEnum $logType The type of log entry (e.g. INFO, ERROR, WARNING).
     * @param array<string, mixed> $data Additional contextual data to include in
     * the log record.
     *
     * @return void
     *
     * @throws JsonException If encoding to JSON fails.
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

        // Better: use $logType->value instead of hardcoding Level::Info
        $streamHandler = new StreamHandler($fileName, $logType->value);

        $logger->pushHandler($streamHandler);

        if (!isset($_SERVER['REQUEST_URI'])) {
            /** @var non-empty-string $json */
            $json = json_encode($message, JSON_THROW_ON_ERROR);
        } else {
            /** @var non-empty-string $json */
            $json = json_encode([
                'uri' => $_SERVER['REQUEST_URI'],
                'data' => json_decode($message),
            ], JSON_THROW_ON_ERROR);
        }

        $logger->$logTypeValue($json, $data);
    }
}

if (!function_exists('json')) {
    /**
     * Converts the given value to a JSON string.
     *
     * @param mixed $values The value to convert to JSON.
     *
     * @return string The encoded JSON string.
     *
     * @throws JsonException If encoding to JSON fails.
     */
    function json(mixed $values): string
    {
        return json_encode(
            $values,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        );
    }
}

if (!function_exists('isError')) {
    /**
     * Checks if the response indicates an error status.
     *
     * <code>
     *     return isError(error('ERR'));
     * </code>
     *
     * @param stdClass $response The response object to check.
     *
     * @return bool True if the response has an error status, otherwise false.
     */
    function isError(stdClass $response): bool
    {
        return !empty($response->status) && in_array($response->status, RESPONSE->getErrors(), true);
    }
}

if (!function_exists('isSuccess')) {
    /**
     * Checks if the response indicates a successful status.
     *
     * <code>
     *     return isSuccess(success('OK'));
     * </code>
     *
     * @param stdClass $response The response object to check.
     *
     * @return bool True if the response has a success status, otherwise false.
     */
    function isSuccess(stdClass $response): bool
    {
        return !empty($response->status) && $response->status === Status::SUCCESS;
    }
}

if (!function_exists('jwt')) {
    /**
     * Gets the JWT token from the HTTP_AUTHORIZATION header.
     *
     * <code>
     *     $token = jwt();
     * </code>
     *
     * @return string|bool The JWT token if available, or false if not found.
     */
    function jwt(): string|bool
    {
        return new JWT()->getJWT();
    }
}

if (!function_exists('fake')) {
    /**
     * Generates a Generator instance to obtain fake data.
     *
     * <code>
     *     $name = fake()->name();
     * </code>
     *
     * @param string $locale The regional configuration to use.
     *
     * @return Generator The Faker generator instance.
     */
    function fake(string $locale = Factory::DEFAULT_LOCALE): Generator
    {
        return Fake::get($locale);
    }
}

if (!function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * <code>
     *     $var = env('DEFAULT_DATABASE');
     * </code>
     *
     * @param string $key The name of the environment variable.
     * @param string|int|float|bool|null $default The default value to return if the
     * variable is not set.
     *
     * @return string|int|float|bool|null The value of the environment variable or
     * the default value.
     */
    function env(string $key, string|int|float|bool|null $default = null): string|int|float|bool|null
    {
        return Env::get($key, $default);
    }
}
