<?php

declare(strict_types=1);

namespace Lion\Bundle\Traits;

use Lion\Request\Request;
use Lion\Request\Response;
use Throwable;

/**
 * Implements the abstract methods necessary to execute an exception
 *
 * @package Lion\Bundle\Traits
 */
trait ExceptionsTrait
{
    /**
     * Construct the exception
     *
     * @param string $message [The Exception message to throw]
     * @param int $code [The Exception code]
     * @param string $status [Response status]
     * @param mixed $data [Response data]
     * @param Throwable|null $previus [The previous exception used for the
     * exception chaining]
     */
    public function __construct(
        string $message = '',
        int $code = Request::HTTP_INTERNAL_SERVER_ERROR,
        string $status = Response::ERROR,
        mixed $data = null,
        ?Throwable $previus = null
    ) {
        $this
            ->setStatus($status)
            ->setData($data);

        parent::__construct($message, $code, $previus);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): mixed
    {
        return response($this->getStatus(), $this->getMessage(), $this->getCode(), $this->getData());
    }
}
