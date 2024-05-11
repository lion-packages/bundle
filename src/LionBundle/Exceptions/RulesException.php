<?php

declare(strict_types=1);

namespace Lion\Bundle\Exceptions;

use JsonSerializable;
use Lion\Bundle\Support\Exceptions\ExceptionSupport;
use Lion\Bundle\Traits\ExceptionsTrait;

/**
 * Exceptions to the rules
 *
 * @package Lion\Bundle\Exceptions
 */
class RulesException extends ExceptionSupport implements JsonSerializable
{
    use ExceptionsTrait;
}
