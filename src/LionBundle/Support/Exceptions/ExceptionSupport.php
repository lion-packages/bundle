<?php

declare(strict_types=1);

namespace Lion\Bundle\Support\Exceptions;

use Exception;
use Lion\Request\Response;

/**
 * Support for exception handling
 *
 * @package Lion\Bundle\Support\Exceptions
 */
class ExceptionSupport extends Exception
{
    /**
     * [Exception response status]
     *
     * @var string $status
     */
    private string $status = Response::ERROR;

    /**
     * [Response data]
     *
     * @var mixed $data
     */
    private mixed $data = null;

    /**
     * Get response status
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * Change the response state of the exception
     *
     * @param string $status [Exception response status]
     *
     * @return ExceptionSupport
     */
    public function setStatus(string $status): ExceptionSupport
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the response data
     *
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * Change response data
     *
     * @param mixed $data [Response data]
     *
     * @return ExceptionSupport
     */
    public function setData(mixed $data): ExceptionSupport
    {
        $this->data = $data;

        return $this;
    }
}
