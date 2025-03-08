<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands\Schedule;

use InvalidArgumentException;
use JsonException;
use Lion\Request\Http;

/**
 * Tasks class to encapsulate tasks in queue
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
     * @var array<int|string, mixed> $data
     */
    private array $data;

    /**
     * Class Constructor
     *
     * @param string|null $namespace [Property for namespace]
     * @param string|null $method [Property for method]
     * @param array<int|string, mixed> $data [Property for data]
     *
     * @throws InvalidArgumentException
     */
    public function __construct(?string $namespace = null, ?string $method = null, array $data = [])
    {
        if (null === $namespace) {
            throw new InvalidArgumentException('Namespace is null', Http::INTERNAL_SERVER_ERROR);
        }

        if (null === $method) {
            throw new InvalidArgumentException('The method is null', Http::INTERNAL_SERVER_ERROR);
        }

        if (empty($data)) {
            throw new InvalidArgumentException('The data is empty', Http::INTERNAL_SERVER_ERROR);
        }

        $this->namespace = $namespace;

        $this->method = $method;

        $this->data = $data;
    }

    /**
     * Returns the task data in a list
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
