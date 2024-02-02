<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Lion\Bundle\Helpers\RedisClient;
use Lion\Test\Test;
use Predis\Client;

class RedisClientTest extends Test
{
    private RedisClient $redisClient;

    protected function setUp(): void
    {
        $this->redisClient = new RedisClient();
    }

    public function testGetClient(): void
    {
        $this->assertInstanceOf(Client::class, $this->redisClient->getClient());
    }

    public function testGetClientWithOptions(): void
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

        $this->assertInstanceOf(Client::class, $this->redisClient->getClient($defaultOptions));
    }
}
