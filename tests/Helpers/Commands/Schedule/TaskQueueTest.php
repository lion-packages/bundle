<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands\Schedule;

use JsonException;
use Lion\Bundle\Helpers\Commands\Schedule\Task;
use Lion\Bundle\Helpers\Commands\Schedule\TaskQueue;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use Predis\Client;
use ReflectionException;
use Tests\Providers\ExampleProvider;

class TaskQueueTest extends Test
{
    private const int TASK_QUEUE_TIME = 3;
    private const array DATA = [
        'name' => 'root',
    ];

    private TaskQueue $taskQueue;
    /**
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        /** @var string $redisScheme */
        $redisScheme = env('REDIS_SCHEME');

        /** @var string $host */
        $host = env('REDIS_HOST');

        /** @var int $port */
        $port = env('REDIS_PORT');

        /** @var string $password */
        $password = env('REDIS_PASSWORD');

        $this->taskQueue = new TaskQueue([
            'scheme' => $redisScheme,
            'host' => $host,
            'port' => $port,
            'parameters' => [
                'password' => $password,
                'database' => TaskQueue::LION_DATABASE,
            ],
        ]);

        $this->initReflection($this->taskQueue);
    }

    /**
     * @throws ReflectionException
     */
    protected function tearDown(): void
    {
        /** @var Client $client */
        $client = $this->getPrivateProperty('client');

        $client->flushall();
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function construct(): void
    {
        $client = $this->getPrivateProperty('client');

        $this->assertInstanceOf(Client::class, $client);
    }

    /**
     * @throws JsonException
     * @throws ReflectionException
     */
    #[Testing]
    public function push(): void
    {
        $this->taskQueue->push(new Task(ExampleProvider::class, 'getArrExample', self::DATA));

        /** @var Client $client */
        $client = $this->getPrivateProperty('client');

        $data = $client->rpop(TaskQueue::LION_TASKS);

        $this->assertIsString($data);
        $this->assertJson($data);

        $json = json_decode($data, true);

        $this->assertIsArray($json);
        $this->assertNotEmpty($json);
        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('namespace', $json);
        $this->assertArrayHasKey('method', $json);
        $this->assertArrayHasKey('data', $json);
        $this->assertIsArray($json['data']);
        $this->assertArrayHasKey('name', $json['data']);
        $this->assertIsString($json['id']);
        $this->assertIsString($json['namespace']);
        $this->assertIsString($json['method']);
        $this->assertIsArray($json['data']);
        $this->assertIsString($json['data']['name']);
        $this->assertSame(ExampleProvider::class, $json['namespace']);
        $this->assertSame('getArrExample', $json['method']);
        $this->assertSame(self::DATA, $json['data']);
    }

    /**
     * @throws JsonException
     */
    #[Testing]
    public function get(): void
    {
        $data = $this->taskQueue
            ->push(new Task(ExampleProvider::class, 'getArrExample', self::DATA))
            ->get();

        $this->assertIsString($data);
        $this->assertJson($data);

        $json = json_decode($data, true);

        $this->assertIsArray($json);
        $this->assertNotEmpty($json);
        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('namespace', $json);
        $this->assertArrayHasKey('method', $json);
        $this->assertArrayHasKey('data', $json);
        $this->assertIsArray($json['data']);
        $this->assertArrayHasKey('name', $json['data']);
        $this->assertIsString($json['id']);
        $this->assertIsString($json['id']);
        $this->assertIsString($json['namespace']);
        $this->assertIsString($json['method']);
        $this->assertIsArray($json['data']);
        $this->assertIsString($json['data']['name']);
        $this->assertSame(ExampleProvider::class, $json['namespace']);
        $this->assertSame('getArrExample', $json['method']);
        $this->assertSame(self::DATA, $json['data']);
    }

    #[Testing]
    public function pause(): void
    {
        $initialTime = microtime(true);

        $this->taskQueue->pause(self::TASK_QUEUE_TIME);

        $elapsedTime = microtime(true) - $initialTime;

        $this->assertEqualsWithDelta(self::TASK_QUEUE_TIME, $elapsedTime, 0.1);
    }
}
