<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Http;

/**
 * Initializes the defined web rules and filters
 *
 * @property array $middleware [List of defined web filters]
 *
 * @package Lion\Bundle\Helpers\Http
 */
class Routes
{
    /**
     * [List of defined web filters]
     *
     * @var array $middleware
     */
    private static array $middleware;

    /**
     * Returns the list of defined web filters
     *
     * @return array
     */
    public static function getMiddleware(): array
    {
        return self::$middleware;
    }

    /**
     * Change the list of defined web filters
     *
     * @param array $middleware [List of defined web filters]
     *
     * @return Routes
     */
    public static function setMiddleware(array $middleware): Routes
    {
        self::$middleware = $middleware;

        return new static();
    }
}
