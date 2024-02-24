<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers;

use Predis\Client;

class RedisClient
{
    public function getClient(?array $options = null): Client
    {
        $defaultOptions = [
            'scheme' => $_ENV['REDIS_SCHEME'],
            'host' => $_ENV['REDIS_HOST'],
            'port' => $_ENV['REDIS_PORT'],
            'parameters' => [
                'password' => $_ENV['REDIS_PASSWORD'],
                'database' => $_ENV['REDIS_DATABASES']
            ]
        ];

        return new Client(null === $options ? $defaultOptions : $options);
    }
}
