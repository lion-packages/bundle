<?php

declare(strict_types=1);

namespace Lion\Bundle\Exceptions;

use JsonSerializable;
use Lion\Bundle\Support\Exceptions\ExceptionSupport;
use Lion\Bundle\Traits\ExceptionsTrait;

/**
 * Exception for middleware errors
 *
 * @package App\Exceptions
 */
class MiddlewareException extends ExceptionSupport implements JsonSerializable
{
    use ExceptionsTrait;
}
