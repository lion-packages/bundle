<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Http;

use Lion\Route\Middleware;

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
     * @var array<int, Middleware> $middleware
     */
    private static array $middleware;

    /**
     * Returns the list of defined web filters
     *
     * @return array<int, Middleware>
     */
    public static function getMiddleware(): array
    {
        return self::$middleware;
    }

    /**
     * Change the list of defined web filters
     *
     * @param array<int, Middleware> $middleware [List of defined web filters]
     *
     * @return void
     */
    public static function setMiddleware(array $middleware): void
    {
        self::$middleware = $middleware;
    }
}
