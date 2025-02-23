<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Http;

/**
 * Initializes the defined web rules and filters
 *
 * @package Lion\Bundle\Helpers\Http
 */
class Routes
{
    /**
     * [List of defined web filters]
     *
     * @var array<string, class-string> $middleware
     */
    private static array $middleware;

    /**
     * Returns the list of defined web filters
     *
     * @return array<string, class-string>
     */
    public static function getMiddleware(): array
    {
        return self::$middleware;
    }

    /**
     * Change the list of defined web filters
     *
     * @param array<string, class-string> $middleware [List of defined web
     * filters]
     *
     * @return void
     */
    public static function setMiddleware(array $middleware): void
    {
        self::$middleware = $middleware;
    }
}
