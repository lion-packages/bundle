<?php

declare(strict_types=1);

namespace Lion\Bundle\Support;

use JsonException;

/**
 * Tasks class to encapsulate tasks in queue.
 *
 * @package Lion\Bundle\Helpers\Commands\Schedule
 */
class Task
{
    /**
     * Property for namespace.
     *
     * @var string $namespace
     */
    private string $namespace;

    /**
     * Property for method.
     *
     * @var string $method
     */
    private string $method;

    /**
     * Property for data.
     *
     * @var array<int|string, mixed> $data
     */
    private array $data;

    /**
     * Class Constructor
     *
     * @param string $namespace Property for namespace.
     * @param string $method Property for method.
     * @param array<int|string, mixed> $data Property for data.
     */
    public function __construct(string $namespace, string $method, array $data = [])
    {
        $this->namespace = $namespace;

        $this->method = $method;

        $this->data = $data;
    }

    /**
     * Returns the task data in a list.
     *
     * @return string
     *
     * @throws JsonException
     */
    public function getTask(): string
    {
        return json([
            'id' => uniqid('task-'),
            'namespace' => $this->namespace,
            'method' => $this->method,
            'data' => $this->data,
        ]);
    }
}
