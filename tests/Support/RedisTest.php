<?php

declare(strict_types=1);

namespace Tests\Support;

use Lion\Bundle\Support\Redis;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use PHPUnit\Framework\Attributes\TestWith;
use Predis\Client;
use ReflectionException;

class RedisTest extends Test
{
    private const int REDIS_TIME_CACHE = 10;

    private Redis $redis;

    /**
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $this->redis = new Redis();

        $this->initReflection($this->redis);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function construct(): void
    {
        $this->assertInstanceOf(Client::class, $this->getPrivateProperty('client'));
    }

    #[Testing]
    public function getClient(): void
    {
        $this->assertInstanceOf(Client::class, $this->redis->getClient());
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function connect(): void
    {
        $this->getPrivateMethod('connect');

        /** @var Client $client */
        $client = $this->getPrivateProperty('client');

        $this->assertIsObject($client);
        $this->assertInstanceOf(Client::class, $client);

        $connected = $client->getConnection()->isConnected();

        $this->assertIsBool($connected);
        $this->assertTrue($connected);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[TestWith(['data' => ['name' => 'root']])]
    #[TestWith(['data' => ['name' => 'lion']])]
    public function toArray(array $data): void
    {
        $return = $this->getPrivateMethod('toArray', [json_encode($data)]);

        $this->assertIsArray($return);
        $this->assertArrayHasKey('name', $return);
        $this->assertIsString($return['name']);
        $this->assertSame($return['name'], $data['name']);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setTime(): void
    {
        $seconds = (int) fake()->numerify('##');

        $this->assertInstanceOf(Redis::class, $this->redis->setTime($seconds));
        $this->assertSame($seconds, $this->getPrivateProperty('seconds'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[TestWith(['key' => 'data', 'json' => ['name' => 'root']])]
    #[TestWith(['key' => 'data', 'json' => ['name' => 'lion']])]
    public function setRedis(string $key, array $json): void
    {
        $this->redis
            ->setTime(self::REDIS_TIME_CACHE)
            ->set($key, $json);

        /** @var Client $client */
        $client = $this->getPrivateProperty('client');

        $return = json_decode($client->get($key), true);

        $this->assertIsArray($return);
        $this->assertArrayHasKey('name', $return);
        $this->assertIsString($return['name']);
        $this->assertSame($json['name'], $return['name']);
    }

    #[Testing]
    #[TestWith(['key' => 'data', 'json' => ['name' => 'root']])]
    #[TestWith(['key' => 'data', 'json' => ['name' => 'lion']])]
    public function expireData(string $key, array $json): void
    {
        $this->redis
            ->setTime(self::REDIS_TIME_CACHE)
            ->set($key, $json);

        sleep(self::REDIS_TIME_CACHE + 1);

        $this->assertNull($this->redis->get($key));
    }

    #[Testing]
    #[TestWith(['key' => 'data', 'json' => ['name' => 'root']])]
    #[TestWith(['key' => 'data', 'json' => ['name' => 'lion']])]
    public function get(string $key, array $json): void
    {
        $return = $this->redis
            ->setTime(self::REDIS_TIME_CACHE)
            ->set($key, $json)
            ->get($key);

        $this->assertIsArray($return);
        $this->assertArrayHasKey('name', $return);
        $this->assertIsString($return['name']);
        $this->assertSame($json['name'], $return['name']);
    }

    #[Testing]
    #[TestWith(['key' => 'data', 'json' => ['name' => 'root']])]
    #[TestWith(['key' => 'data', 'json' => ['name' => 'lion']])]
    public function del(string $key, array $json): void
    {
        $value = $this->redis
            ->setTime(self::REDIS_TIME_CACHE)
            ->set($key, $json)
            ->del($key)
            ->get($key);

        $this->assertNull($value);
    }

    #[Testing]
    #[TestWith(['key' => 'data', 'json' => ['name' => 'root']])]
    #[TestWith(['key' => 'data', 'json' => ['name' => 'lion']])]
    public function empty(string $key, array $json): void
    {
        $return = $this->redis
            ->setTime(self::REDIS_TIME_CACHE)
            ->set($key, $json)
            ->get($key);

        $this->assertIsArray($return);
        $this->assertArrayHasKey('name', $return);
        $this->assertIsString($return['name']);
        $this->assertSame($json['name'], $return['name']);

        $this->redis->empty();

        $this->assertNull($this->redis->get($key));
    }
}
