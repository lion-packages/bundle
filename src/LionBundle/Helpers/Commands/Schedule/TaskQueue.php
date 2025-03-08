<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands\Schedule;

use DI\Attribute\Inject;
use JsonException;
use Lion\Bundle\Helpers\Redis;

/**
 * Manage server queued task processes
 *
 * @property Redis $redis [Process Manager with Redis Driver]
 *
 * @package Lion\Bundle\Helpers\Commands\Schedule
 */
class TaskQueue
{
    /**
     * [Defines the property that contains the queued task data]
     *
     * @const LION_TASKS
     */
    public const string LION_TASKS  = 'lion-tasks';

    /**
     * [Process Manager with Redis Driver]
     *
     * @var Redis $redis
     */
    private Redis $redis;

    #[Inject]
    public function setRedis(Redis $redis): TaskQueue
    {
        $this->redis = $redis;

        return $this;
    }

    /**
     * Add a task to the queue
     *
     * @param Task $task [Tasks class to encapsulate tasks in queue]
     *
     * @return TaskQueue
     *
     * @throws JsonException
     */
    public function push(Task $task): TaskQueue
    {
        $this->redis
            ->getClient()
            /** @phpstan-ignore-next-line */
            ->lpush(self::LION_TASKS, $task->getTask());

        return $this;
    }

    /**
     * Gets a function that is executed
     *
     * @return string|null
     */
    public function get(): ?string
    {
        return $this->redis
            ->getClient()
            ->rpop(self::LION_TASKS);
    }

    /**
     * Pause the process for a certain time in seconds
     *
     * @param int $time [Amount of time in seconds]
     *
     * @return void
     */
    public function pause(int $time): void
    {
        sleep($time);

        flush();
    }
}
