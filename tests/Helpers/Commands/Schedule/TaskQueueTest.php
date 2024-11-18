<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands\Schedule;

use Lion\Bundle\Helpers\Commands\Schedule\Task;
use Lion\Bundle\Helpers\Commands\Schedule\TaskQueue;
use Lion\Bundle\Helpers\Redis;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Tests\Providers\ExampleProvider;

class TaskQueueTest extends Test
{
    private const int TASK_QUEUE_TIME = 3;
    private const array DATA = [
        'name' => 'root',
    ];

    private TaskQueue $taskQueue;
    private Redis $redis;

    /**
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $this->redis = new Redis();

        $this->taskQueue = (new TaskQueue())
            ->setRedis($this->redis);

        $this->initReflection($this->taskQueue);
    }

    protected function tearDown(): void
    {
        $this->redis->empty();
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setRedis(): void
    {
        $this->assertInstanceOf(TaskQueue::class, $this->taskQueue->setRedis(new Redis()));
        $this->assertInstanceOf(Redis::class, $this->getPrivateProperty('redis'));
    }

    #[Testing]
    public function push(): void
    {
        $this->taskQueue
            ->push(new Task(ExampleProvider::class, 'getArrExample', self::DATA));

        $data = $this->redis->getClient()->rpop(TaskQueue::LION_TASKS);

        $this->assertIsString($data);
        $this->assertJson($data);

        $json = json_decode($data, true);

        $this->assertIsArray($json);
        $this->assertNotEmpty($json);
        $this->assertArrayHasKey('id', $json);
        $this->assertArrayHasKey('namespace', $json);
        $this->assertArrayHasKey('method', $json);
        $this->assertArrayHasKey('data', $json);
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

    #[Testing]
    public function pause(): void
    {
        $initialDate = now();

        $this->taskQueue->pause(self::TASK_QUEUE_TIME);

        $this->assertSame(self::TASK_QUEUE_TIME,  now()->diffInSeconds($initialDate));
    }
}
