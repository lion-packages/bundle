<?php

declare(strict_types=1);

namespace Tests;

use Lion\Test\Test;
use Predis\Client;
use Predis\Response\Status;
use Tests\Providers\EnviromentProviderTrait;

class RedisConnectionTest extends Test
{
    use EnviromentProviderTrait;

    private Client $client;

    protected function setUp(): void
    {
        $this->loadEnviroment();

        $this->client = new Client([
            'scheme' => $_ENV['REDIS_SCHEME'],
            'host' => $_ENV['REDIS_HOST'],
            'port' => $_ENV['REDIS_PORT'],
            'parameters' => [
                'password' => $_ENV['REDIS_PASSWORD'],
                'database' => $_ENV['REDIS_DATABASES']
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
