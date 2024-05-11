<?php

declare(strict_types=1);

namespace Tests\Traits;

use Lion\Request\Request;
use Lion\Test\Test;
use Tests\Providers\Support\Exceptions\ExceptionSupportProvider;

class ExceptionsTraitTest extends Test
{
    const MESSAGE = 'ERR';

    public function testConstruct(): void
    {
        $this->expectException(ExceptionSupportProvider::class);
        $this->expectExceptionMessage(self::MESSAGE);
        $this->expectExceptionCode(Request::HTTP_NOT_FOUND);

        throw new ExceptionSupportProvider(self::MESSAGE, Request::HTTP_NOT_FOUND);
    }
}
