<?php

declare(strict_types=1);

namespace Lion\Bundle\Exceptions;

use Lion\Exceptions\Exception;
use Lion\Exceptions\Interfaces\ExceptionInterface;
use Lion\Exceptions\Traits\ExceptionTrait;

/**
 * Exception for middleware errors
 *
 * @package Lion\Bundle\Exceptions
 */
class MiddlewareException extends Exception implements ExceptionInterface
{
    use ExceptionTrait;
}
