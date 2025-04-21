<?php

declare(strict_types=1);

namespace Tests\Exceptions;

use Lion\Bundle\Exceptions\MiddlewareException;
use Lion\Request\Http;
use Lion\Request\Status;
use Lion\Test\Test;

class MiddlewareExceptionTest extends Test
{
    public const MESSAGE = 'ERR';

    public function testMiddlewareException(): void
    {
        $this
            ->exception(MiddlewareException::class)
            ->exceptionMessage(self::MESSAGE)
            ->exceptionStatus(Status::ERROR)
            ->exceptionCode(Http::INTERNAL_SERVER_ERROR)
            ->expectLionException();
    }
}
