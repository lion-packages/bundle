<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands\Schedule;

use Lion\Bundle\Commands\Lion\Schedule\ScheduleSchemaCommand;
use Lion\Bundle\Enums\TaskStatusEnum;
use Lion\Bundle\Helpers\Commands\Schedule\TaskQueue;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Database\Drivers\MySQL as DB;
use Lion\Database\Drivers\Schema\MySQL as Schema;
use Lion\Dependency\Injection\Container;
use Lion\Request\Response;
use Lion\Test\Test;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\Providers\ConnectionProviderTrait;

class TaskQueueTest extends Test
{
    use ConnectionProviderTrait;

    const MESSAGE = 'the schema for queued tasks has been created';
    const TASK_QUEUE_NAME = 'send:email:test';
    const TASK_QUEUE_TIME = 3;

    private TaskQueue $taskQueue;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->runDatabaseConnections();

        $this->taskQueue = new TaskQueue();

        $this->initReflection($this->taskQueue);

        $application = (new Kernel())->getApplication();

        $application->add((new Container())->injectDependencies(new ScheduleSchemaCommand()));

        $this->commandTester = new CommandTester($application->find('schedule:schema'));
    }

    protected function tearDown(): void
    {
        Schema::dropTable('task_queue')->execute();
    }

    public function testAdd(): void
    {
        $callable = function (object $object): void {
            $name = null;
        };

        $this->taskQueue->add(self::TASK_QUEUE_NAME, $callable);

        $functions = $this->getPrivateProperty('functions');

        $this->assertIsArray($functions);
        $this->assertArrayHasKey(self::TASK_QUEUE_NAME, $functions);
        $this->assertSame($callable, $functions[self::TASK_QUEUE_NAME]);
    }

    public function testGet(): void
    {
        $callable = function (object $object): void {
            $name = null;
        };

        $this->taskQueue->add(self::TASK_QUEUE_NAME, $callable);

        $closure = $this->taskQueue->get(self::TASK_QUEUE_NAME);

        $this->assertSame($callable, $closure);
    }

    public function testPush(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->setInputs(['0'])->execute([]));
        $this->assertStringContainsString(self::MESSAGE, $this->commandTester->getDisplay());

        $tasks = DB::table('task_queue')->select()->getAll();

        $this->assertIsObject($tasks);
        $this->assertObjectHasProperty('status', $tasks);
        $this->assertObjectHasProperty('message', $tasks);
        $this->assertSame(Response::SUCCESS, $tasks->status);
        $this->assertSame('no data available', $tasks->message);

        $queueType = 'send:email';

        $json = json([
            'email' => 'root@dev.com'
        ]);

        $this->taskQueue->push($queueType, $json);

        $tasks = DB::table('task_queue')->select()->getAll();

        $this->assertIsArray($tasks);
        $this->assertCount(1, $tasks);

        $row = reset($tasks);

        $this->assertIsObject($row);
        $this->assertObjectHasProperty('idtask_queue', $row);
        $this->assertObjectHasProperty('task_queue_type', $row);
        $this->assertObjectHasProperty('task_queue_data', $row);
        $this->assertObjectHasProperty('task_queue_status', $row);
        $this->assertObjectHasProperty('task_queue_attempts', $row);
        $this->assertObjectHasProperty('task_queue_create_at', $row);
        $this->assertSame($queueType, $row->task_queue_type);
        $this->assertJsonStringEqualsJsonString($json, $row->task_queue_data);
        $this->assertSame(TaskStatusEnum::PENDING->value, $row->task_queue_status);
    }

    public function testEdit(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->setInputs(['0'])->execute([]));
        $this->assertStringContainsString(self::MESSAGE, $this->commandTester->getDisplay());

        $tasks = DB::table('task_queue')->select()->getAll();

        $this->assertIsObject($tasks);
        $this->assertObjectHasProperty('status', $tasks);
        $this->assertObjectHasProperty('message', $tasks);
        $this->assertSame(Response::SUCCESS, $tasks->status);
        $this->assertSame('no data available', $tasks->message);

        $queueType = 'send:email';

        $json = json([
            'email' => 'root@dev.com'
        ]);

        $this->taskQueue->push($queueType, $json);

        $tasks = DB::table('task_queue')->select()->getAll();

        $this->assertIsArray($tasks);
        $this->assertCount(1, $tasks);

        $queue = reset($tasks);

        $this->assertIsObject($queue);
        $this->assertObjectHasProperty('idtask_queue', $queue);
        $this->assertObjectHasProperty('task_queue_type', $queue);
        $this->assertObjectHasProperty('task_queue_data', $queue);
        $this->assertObjectHasProperty('task_queue_status', $queue);
        $this->assertObjectHasProperty('task_queue_attempts', $queue);
        $this->assertObjectHasProperty('task_queue_create_at', $queue);
        $this->assertSame($queueType, $queue->task_queue_type);
        $this->assertJsonStringEqualsJsonString($json, $queue->task_queue_data);
        $this->assertSame(TaskStatusEnum::PENDING->value, $queue->task_queue_status);

        $this->taskQueue->edit($queue, TaskStatusEnum::IN_PROGRESS);

        $tasks = DB::table('task_queue')->select()->getAll();

        $this->assertIsArray($tasks);
        $this->assertCount(1, $tasks);

        $queue = reset($tasks);

        $this->assertIsObject($queue);
        $this->assertObjectHasProperty('idtask_queue', $queue);
        $this->assertObjectHasProperty('task_queue_type', $queue);
        $this->assertObjectHasProperty('task_queue_data', $queue);
        $this->assertObjectHasProperty('task_queue_status', $queue);
        $this->assertObjectHasProperty('task_queue_attempts', $queue);
        $this->assertObjectHasProperty('task_queue_create_at', $queue);
        $this->assertSame($queueType, $queue->task_queue_type);
        $this->assertJsonStringEqualsJsonString($json, $queue->task_queue_data);
        $this->assertSame(TaskStatusEnum::IN_PROGRESS->value, $queue->task_queue_status);
    }

    public function testRemove(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->setInputs(['0'])->execute([]));
        $this->assertStringContainsString(self::MESSAGE, $this->commandTester->getDisplay());

        $tasks = DB::table('task_queue')->select()->getAll();

        $this->assertIsObject($tasks);
        $this->assertObjectHasProperty('status', $tasks);
        $this->assertObjectHasProperty('message', $tasks);
        $this->assertSame(Response::SUCCESS, $tasks->status);
        $this->assertSame('no data available', $tasks->message);

        $queueType = 'send:email';

        $json = json([
            'email' => 'root@dev.com'
        ]);

        $this->taskQueue->push($queueType, $json);

        $tasks = DB::table('task_queue')->select()->getAll();

        $this->assertIsArray($tasks);
        $this->assertCount(1, $tasks);

        $queue = reset($tasks);

        $this->assertIsObject($queue);
        $this->assertObjectHasProperty('idtask_queue', $queue);
        $this->assertObjectHasProperty('task_queue_type', $queue);
        $this->assertObjectHasProperty('task_queue_data', $queue);
        $this->assertObjectHasProperty('task_queue_status', $queue);
        $this->assertObjectHasProperty('task_queue_attempts', $queue);
        $this->assertObjectHasProperty('task_queue_create_at', $queue);
        $this->assertSame($queueType, $queue->task_queue_type);
        $this->assertJsonStringEqualsJsonString($json, $queue->task_queue_data);
        $this->assertSame(TaskStatusEnum::PENDING->value, $queue->task_queue_status);

        $this->taskQueue->remove($queue);

        $tasks = DB::table('task_queue')->select()->getAll();

        $this->assertIsObject($tasks);
        $this->assertObjectHasProperty('status', $tasks);
        $this->assertObjectHasProperty('message', $tasks);
        $this->assertSame(Response::SUCCESS, $tasks->status);
        $this->assertSame('no data available', $tasks->message);
    }

    public function testPause(): void
    {
        $initialDate = now();

        $this->taskQueue->pause(self::TASK_QUEUE_TIME);

        $this->assertSame(self::TASK_QUEUE_TIME,  now()->diffInSeconds($initialDate));
    }
}
