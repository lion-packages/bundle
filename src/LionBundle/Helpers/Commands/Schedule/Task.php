<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands\Schedule;

use InvalidArgumentException;
use Lion\Request\Http;

/**
 * Tasks class to encapsulate tasks in queue
 *
 * @property string|null $namespace [Property for namespace]
 * @property string|null $method [Property for method]
 * @property array|null $data [Property for data]
 *
 * @package Lion\Bundle\Helpers\Commands\Schedule
 */
class Task
{
    /**
     * [Property for namespace]
     *
     * @var string|null $namespace
     */
    private ?string $namespace = null;

    /**
     * [Property for method]
     *
     * @var string|null $method
     */
    private ?string $method = null;

    /**
     * [Property for data]
     *
     * @var array|null $data
     */
    private ?array $data = null;

    /**
     * Class Constructor
     *
     * @param string|null $namespace [Property for namespace]
     * @param string|null $method [Property for method]
     * @param array|null $data [Property for data]
     */
    public function __construct(?string $namespace = null, ?string $method = null, ?array $data = [])
    {
        if (NULL_VALUE === $namespace) {
            throw new InvalidArgumentException('namespace is null', Http::INTERNAL_SERVER_ERROR);
        }

        if (NULL_VALUE === $method) {
            throw new InvalidArgumentException('method is null', Http::INTERNAL_SERVER_ERROR);
        }

        if (NULL_VALUE === $data) {
            throw new InvalidArgumentException('data is null', Http::INTERNAL_SERVER_ERROR);
        }

        $this->namespace = $namespace;

        $this->method = $method;

        $this->data = $data;
    }

    /**
     * Returns the task data in a list
     *
     * @return string
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
