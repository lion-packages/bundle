<?php

declare(strict_types=1);

namespace Tests\Exceptions;

use Lion\Bundle\Exceptions\MiddlewareException;
use Lion\Exceptions\Exception;
use Lion\Request\Http;
use Lion\Request\Status;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;

class MiddlewareExceptionTest extends Test
{
    public const string ERR_MESSAGE = 'ERR';

    /**
     * @throws Exception if validation or instantiation of the exception fails.
     */
    #[Testing]
    public function middlewareException(): void
    {
        $this
            ->exception(MiddlewareException::class)
            ->exceptionMessage(self::ERR_MESSAGE)
            ->exceptionStatus(Status::ERROR)
            ->exceptionCode(Http::INTERNAL_SERVER_ERROR)
            ->expectLionException();
    }
}
