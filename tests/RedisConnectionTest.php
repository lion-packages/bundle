<?php

declare(strict_types=1);

namespace Tests;

use Lion\Test\Test;
use Predis\Client;
use Predis\Response\Status;

class RedisConnectionTest extends Test
{
    private Client $client;

    protected function setUp(): void
    {
        $this->client = new Client([
            'scheme' => env->REDIS_SCHEME,
            'host' => env->REDIS_HOST,
            'port' => env->REDIS_PORT,
            'parameters' => [
                'password' => env->REDIS_PASSWORD,
                'database' => env->REDIS_DATABASES
            ]
        ]);
    }

    public function testRedisClient(): void
    {
        $this->assertInstanceOf(Client::class, $this->client);
    }

    public function testSet(): void
    {
        $this->assertInstanceOf(Status::class, $this->client->set('foo', 'bar'));
    }

    public function testGet(): void
    {
        $this->assertInstanceOf(Status::class, $this->client->set('foo', 'bar'));
        $this->assertSame('bar', $this->client->get('foo'));
    }

    public function testDel(): void
    {
        $this->assertInstanceOf(Status::class, $this->client->set('foo', 'bar'));
        $this->assertSame('bar', $this->client->get('foo'));

        $this->client->del('foo');

        $this->assertNull($this->client->get('bar'));
    }
}
