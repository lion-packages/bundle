<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands\Schedule;

use InvalidArgumentException;
use Lion\Bundle\Helpers\Commands\Schedule\Task;
use Lion\Request\Http;
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

        $this->assertInstanceOf(Task::class, $task);
        $this->assertSame(self::class, $this->getPrivateProperty('namespace'));
        $this->assertSame('construct', $this->getPrivateProperty('method'));
        $this->assertSame(['foo' => 'bar'], $this->getPrivateProperty('data'));
    }

    #[Testing]
    public function constructInvalidNamespace(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('namespace is null');
        $this->expectExceptionCode(Http::INTERNAL_SERVER_ERROR);

        new Task();
    }

    #[Testing]
    public function constructInvalidMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('method is null');
        $this->expectExceptionCode(Http::INTERNAL_SERVER_ERROR);

        new Task(self::class);
    }

    #[Testing]
    public function constructInvalidData(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('data is null');
        $this->expectExceptionCode(Http::INTERNAL_SERVER_ERROR);

        new Task(self::class, 'construct', null);
    }

    #[Testing]
    public function getTask(): void
    {
        $task = new Task(self::class, 'construct', ['foo' => 'bar']);

        $this->assertInstanceOf(Task::class, $task);

        $json = $task->getTask();

        $this->assertIsString($json);
        $this->assertJson($json);

        $data = json_decode($json, true);

        $this->assertIsString($data['id']);
        $this->assertIsString($data['namespace']);
        $this->assertIsString($data['method']);
        $this->assertIsArray($data['data']);
        $this->assertSame(self::class, $data['namespace']);
        $this->assertSame('construct', $data['method']);
        $this->assertSame(['foo' => 'bar'], $data['data']);
    }
}
