<?php

declare(strict_types=1);

namespace Tests\Exceptions;

use Lion\Bundle\Exceptions\RulesException;
use Lion\Request\Http;
use Lion\Request\Status;
use Lion\Test\Test;

class RulesExceptionTest extends Test
{
    const MESSAGE = 'ERR';

    public function testRulesException(): void
    {
        $this
            ->exception(RulesException::class)
            ->exceptionMessage(self::MESSAGE)
            ->exceptionStatus(Status::ERROR)
            ->exceptionCode(Http::INTERNAL_SERVER_ERROR)
            ->expectLionException();
    }
}
