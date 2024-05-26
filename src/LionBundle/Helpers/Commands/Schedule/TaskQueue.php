<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands\Schedule;

use Closure;
use Exception;
use Lion\Bundle\Enums\TaskStatusEnum;
use Lion\Database\Drivers\MySQL as DB;
use Lion\Request\Http;

/**
 * Manage server queued task processes
 *
 * @property array<string, Closure|array<string, string>> $functions [List of processes to run in task
 * list]
 *
 * @package Lion\Bundle\Helpers\Commands\Schedule
 */
class TaskQueue
{
    /**
     * [List of processes to run in task list]
     *
     * @var array<string, Closure|array<string, string>> $functions
     */
    private static array $functions = [];

    /**
     * Add the list of processes to run in the task list
     *
     * @param string $name [Name of the function being executed]
     * @param Closure|array<string, string> $callable [Function that is executed]
     *
     * @return void
     */
    public static function add(string $name, Closure|array $callable): void
    {
        self::$functions[$name] = $callable;
    }

    /**
     * Gets a function that is executed
     *
     * @param string $name [Name of the function being executed]
     *
     * @return Closure|array<string, string>
     */
    public static function get(string $name): Closure|array
    {
        return self::$functions[$name];
    }

    /**
     * Add a task to queue
     *
     * @param string $queueType [queued task type]
     * @param string $json [data in json format]
     *
     * @return void
     *
     * @throws Exception [Catch an exception if the task is not inserted into
     * the queue]
     */
    public static function push(string $queueType, string $json): void
    {
        $response = DB::table('task_queue')
            ->insert([
                'task_queue_type' => $queueType,
                'task_queue_data' => $json,
                'task_queue_status' => TaskStatusEnum::PENDING->value,
                'task_queue_attempts' => 0
            ])
            ->execute();

        if (isError($response)) {
            throw new Exception($response->message, Http::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update a queued task
     *
     * @param object $queue [Queued task object]
     * @param TaskStatusEnum $taskStatusEnum [Queued task status]
     *
     * @return void
     *
     * @throws Exception [Catch an exception if the task is not inserted into
     * the queue]
     */
    public static function edit(object $queue, TaskStatusEnum $taskStatusEnum): void
    {
        $response = DB::table('task_queue')
            ->update([
                'task_queue_status' => $taskStatusEnum->value
            ])
            ->where()->equalTo('idtask_queue', $queue->idtask_queue)
            ->execute();

        if (isError($response)) {
            throw new Exception($response->message, Http::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete a task from the queue
     *
     * @param object $queue [Queued task object]
     *
     * @return void
     *
     * @throws Exception [Catch an exception if the task is not inserted into
     * the queue]
     */
    public static function remove(object $queue): void
    {
        $response = DB::table('task_queue')
            ->delete()
            ->where()->equalTo('idtask_queue', $queue->idtask_queue)
            ->execute();

        if (isError($response)) {
            throw new Exception($response->message, Http::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Pause the process for a certain time in seconds
     *
     * @param int $time [Amount of time in seconds]
     *
     * @return void
     */
    public static function pause(int $time): void
    {
        sleep($time);

        flush();
    }
}
