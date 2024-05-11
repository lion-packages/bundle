<?php

declare(strict_types=1);

namespace Tests\Providers\Support\Exceptions;

use JsonSerializable;
use Lion\Bundle\Support\Exceptions\ExceptionSupport;
use Lion\Bundle\Traits\ExceptionsTrait;

class ExceptionSupportProvider extends ExceptionSupport implements JsonSerializable
{
    use ExceptionsTrait;
}
