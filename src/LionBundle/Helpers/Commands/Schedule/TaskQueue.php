<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands\Schedule;

use JsonException;
use Lion\Bundle\Support\Task;
use Predis\Client;

/**
 * Manage server queued task processes
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
    public const string LION_TASKS = 'lion-tasks';

    /**
     * [Defines the database to connect to and manipulate tasks]
     *
     * @const LION_DATABASE
     */
    public const int LION_DATABASE = 0;

    /**
     * [Client class used for connecting and executing commands on Redis]
     *
     * @var Client $client
     */
    private Client $client;

    /**
     * Class constructor
     *
     * @param array{
     *     scheme: string,
     *     host: string,
     *     port: int,
     *     parameters: array{
     *         password: string,
     *         database: int
     *     }
     * } $parameters
     */
    public function __construct(array $parameters)
    {
        $this->client = new Client($parameters);
    }

    /**
     * Add one or more tasks to the queue
     *
     * @param Task $task [First task to add]
     * @param Task ...$tasks [Additional tasks to add]
     *
     * @return TaskQueue
     *
     * @throws JsonException
     */
    public function push(Task $task, Task ...$tasks): TaskQueue
    {
        $this->client
            /** @phpstan-ignore-next-line */
            ->lpush(self::LION_TASKS, $task->getTask());

        foreach ($tasks as $additionalTask) {
            $this->client
                /** @phpstan-ignore-next-line */
                ->lpush(self::LION_TASKS, $additionalTask->getTask());
        }

        return $this;
    }

    /**
     * Gets a function that is executed
     *
     * @return string|null
     */
    public function get(): ?string
    {
        return $this->client->rpop(self::LION_TASKS);
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
