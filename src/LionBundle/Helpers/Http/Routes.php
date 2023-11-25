<?php

declare(strict_types=1);

namespace LionBundle\Helpers\Http;

class Routes
{
    private static array $rules;
    private static array $middleware;

    public static function getRules(): array
    {
        return self::$rules;
    }

    public static function setRules(array $rules): void
    {
        self::$rules = $rules;
    }

    public static function getMiddleware(): array
    {
        return self::$middleware;
    }

    public static function setMiddleware(array $middleware): void
    {
        self::$middleware = $middleware;
    }
}
