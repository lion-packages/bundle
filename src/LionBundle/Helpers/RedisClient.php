<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers;

use Predis\Client;

class RedisClient
{
    public function getClient(?array $options = null): Client
    {
        $defaultOptions = [
            'scheme' => env->REDIS_SCHEME,
            'host' => env->REDIS_HOST,
            'port' => env->REDIS_PORT,
            'parameters' => [
                'password' => env->REDIS_PASSWORD,
                'database' => env->REDIS_DATABASES
            ]
        ];

        return new Client(null === $options ? $defaultOptions : $options);
    }
}
