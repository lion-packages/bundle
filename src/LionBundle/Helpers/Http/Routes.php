<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Http;

class Routes
{
    private static array $rules;
    private static array $middleware;

    public static function getRules(): array
    {
        return self::$rules;
    }

    public static function setRules(array $rules): Routes
    {
        self::$rules = $rules;

        return new static;
    }

    public static function getMiddleware(): array
    {
        return self::$middleware;
    }

    public static function setMiddleware(array $middleware): Routes
    {
        self::$middleware = $middleware;

        return new static;
    }
}
