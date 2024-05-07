<?php

declare(strict_types=1);

namespace Tests\Exceptions;


use Lion\Bundle\Exceptions\RulesException;
use Lion\Request\Request;
use Lion\Test\Test;

class RulesExceptionTest extends Test
{
    const MESSAGE = 'ERR';

    public function testRulesException(): void
    {
        $this->expectException(RulesException::class);
        $this->expectExceptionCode(Request::HTTP_INTERNAL_SERVER_ERROR);
        $this->expectExceptionMessage(self::MESSAGE);

        throw new RulesException(self::MESSAGE, Request::HTTP_INTERNAL_SERVER_ERROR);
    }
}
