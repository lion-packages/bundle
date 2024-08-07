<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands\Schedule;

use Closure;
use Exception;
use Lion\Bundle\Enums\TaskStatusEnum;
use Lion\Bundle\Helpers\Commands\Schedule\TaskQueue;
use Lion\Command\Command;
use Lion\Database\Drivers\MySQL as DB;
use Lion\Database\Drivers\Schema\MySQL as Schema;
use Lion\Request\Http;
use Lion\Request\Status;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Providers\Commands\TaskQueueSchemaProviderTrait;
use Tests\Providers\ConnectionProviderTrait;
use Tests\Providers\Helpers\Schedule\TaskQueueProviderTrait;

class TaskQueueTest extends Test
{
    use TaskQueueSchemaProviderTrait;
    use ConnectionProviderTrait;
    use TaskQueueProviderTrait;

    private const string MESSAGE = 'the schema for queued tasks has been created';
    private const string TASK_QUEUE_NAME = 'send:email:test';
    private const int TASK_QUEUE_TIME = 3;

    private TaskQueue $taskQueue;

    private array $backupConnections;

    protected function setUp(): void
    {
        $this->runDatabaseConnections();

        $this->taskQueue = new TaskQueue();

        $this->initReflection($this->taskQueue);

        $this->backupConnections = DB::getConnections();
    }

    protected function tearDown(): void
    {
        Schema::dropTable('task_queue')->execute();

        DB::run([
            'default' => env('DB_NAME'),
            'connections' => $this->backupConnections,
        ]);
    }

    #[DataProvider('addProvider')]
    public function testAdd(Closure|array $callable): void
    {
        $this->taskQueue->add(self::TASK_QUEUE_NAME, $callable);

        $functions = $this->getPrivateProperty('functions');

        $this->assertIsArray($functions);
        $this->assertArrayHasKey(self::TASK_QUEUE_NAME, $functions);
        $this->assertSame($callable, $functions[self::TASK_QUEUE_NAME]);
    }

    #[DataProvider('addProvider')]
    public function testGet(Closure|array $callable): void
    {
        $this->taskQueue->add(self::TASK_QUEUE_NAME, $callable);

        $closure = $this->taskQueue->get(self::TASK_QUEUE_NAME);

        $this->assertSame($callable, $closure);
    }

    public function testPush(): void
    {
        $this->createTaskQueueSchema();

        $tasks = DB::table('task_queue')->select()->getAll();

        $this->assertIsObject($tasks);
        $this->assertObjectHasProperty('status', $tasks);
        $this->assertObjectHasProperty('message', $tasks);
        $this->assertSame(Status::SUCCESS, $tasks->status);
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

    public function testPushWithException(): void
    {
        $connections = DB::getConnections();

        $this->assertArrayHasKey('lion_database', $connections);
        $this->assertArrayHasKey('dbname', $connections['lion_database']);

        $code = uniqid('code-');

        $connections['lion_database']['dbname'] = $code;

        DB::addConnection('lion_database', $connections['lion_database']);

        $connections = DB::getConnections();

        $this->assertArrayHasKey('lion_database', $connections);
        $this->assertSame($code, $connections['lion_database']['dbname']);
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Http::INTERNAL_SERVER_ERROR);

        $this->taskQueue->push('send:email', json([
            'email' => 'root@dev.com'
        ]));
    }

    public function testEdit(): void
    {
        $this->createTaskQueueSchema();

        $tasks = DB::table('task_queue')->select()->getAll();

        $this->assertIsObject($tasks);
        $this->assertObjectHasProperty('status', $tasks);
        $this->assertObjectHasProperty('message', $tasks);
        $this->assertSame(Status::SUCCESS, $tasks->status);
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

    public function testEditWithException(): void
    {
        $connections = DB::getConnections();

        $this->assertArrayHasKey('lion_database', $connections);
        $this->assertArrayHasKey('dbname', $connections['lion_database']);

        $code = uniqid('code-');

        $connections['lion_database']['dbname'] = $code;

        DB::addConnection('lion_database', $connections['lion_database']);

        $connections = DB::getConnections();

        $this->assertArrayHasKey('lion_database', $connections);
        $this->assertSame($code, $connections['lion_database']['dbname']);
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Http::INTERNAL_SERVER_ERROR);

        $this->taskQueue->edit((object) ['idtask_queue' => 1], TaskStatusEnum::FAILED);
    }

    public function testRemove(): void
    {
        $this->createTaskQueueSchema();

        $tasks = DB::table('task_queue')->select()->getAll();

        $this->assertIsObject($tasks);
        $this->assertObjectHasProperty('status', $tasks);
        $this->assertObjectHasProperty('message', $tasks);
        $this->assertSame(Status::SUCCESS, $tasks->status);
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
        $this->assertSame(Status::SUCCESS, $tasks->status);
        $this->assertSame('no data available', $tasks->message);
    }

    public function testRemoveWithException(): void
    {
        $connections = DB::getConnections();

        $this->assertArrayHasKey('lion_database', $connections);
        $this->assertArrayHasKey('dbname', $connections['lion_database']);

        $code = uniqid('code-');

        $connections['lion_database']['dbname'] = $code;

        DB::addConnection('lion_database', $connections['lion_database']);

        $connections = DB::getConnections();

        $this->assertArrayHasKey('lion_database', $connections);
        $this->assertSame($code, $connections['lion_database']['dbname']);
        $this->expectException(Exception::class);
        $this->expectExceptionCode(Http::INTERNAL_SERVER_ERROR);

        $this->taskQueue->remove((object) ['idtask_queue' => 1]);
    }

    public function testPause(): void
    {
        $initialDate = now();

        $this->taskQueue->pause(self::TASK_QUEUE_TIME);

        $this->assertSame(self::TASK_QUEUE_TIME,  now()->diffInSeconds($initialDate));
    }
}
