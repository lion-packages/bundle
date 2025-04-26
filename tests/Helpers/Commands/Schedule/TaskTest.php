<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands\Schedule;

use JsonException;
use Lion\Bundle\Helpers\Commands\Schedule\Task;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;

class TaskTest extends Test
{
    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function construct(): void
    {
        $task = new Task(self::class, 'construct', ['foo' => 'bar']);

        $this->initReflection($task);

        $this->assertSame(self::class, $this->getPrivateProperty('namespace'));
        $this->assertSame('construct', $this->getPrivateProperty('method'));
        $this->assertSame(['foo' => 'bar'], $this->getPrivateProperty('data'));
    }

    /**
     * @throws JsonException
     */
    #[Testing]
    public function getTask(): void
    {
        $task = new Task(self::class, 'construct', [
            'foo' => 'bar',
        ]);

        $json = $task->getTask();

        $this->assertJson($json);

        $data = json_decode($json, true);

        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('namespace', $data);
        $this->assertArrayHasKey('method', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertIsString($data['id']);
        $this->assertIsString($data['namespace']);
        $this->assertIsString($data['method']);
        $this->assertIsArray($data['data']);
        $this->assertNotEmpty($data['data']);
        $this->assertSame(self::class, $data['namespace']);
        $this->assertSame('construct', $data['method']);
        $this->assertSame(['foo' => 'bar'], $data['data']);
    }
}
