<?php

declare(strict_types=1);

namespace Tests\Middleware;

use Lion\Bundle\Exceptions\MiddlewareException;
use Lion\Bundle\Middleware\HttpsMiddleware;
use Lion\Exceptions\Exception;
use Lion\Request\Http;
use Lion\Request\Status;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;

class HttpsMiddlewareTest extends Test
{
    private HttpsMiddleware $httpsMiddleware;

    protected function setUp(): void
    {
        $this->httpsMiddleware = new HttpsMiddleware();
    }

    /**
     * @throws Exception
     */
    #[Testing]
    public function httpsNotExistHttps(): void
    {
        $this
            ->exception(MiddlewareException::class)
            ->exceptionMessage('the HTTPS protocol header is not set, the connection must be secure (HTTPS)')
            ->exceptionStatus(Status::ERROR)
            ->exceptionCode(Http::FORBIDDEN)
            ->expectLionException(function (): void {
                $this->httpsMiddleware->https();
            });
    }

    /**
     * @throws Exception
     */
    #[Testing]
    public function httpsNotValidHttps(): void
    {
        $this
            ->exception(MiddlewareException::class)
            ->exceptionMessage('the connection is not marked as secure (HTTPS is not active)')
            ->exceptionStatus(Status::ERROR)
            ->exceptionCode(Http::FORBIDDEN)
            ->expectLionException(function (): void {
                $_SERVER['HTTPS'] = 'off';

                $this->httpsMiddleware->https();
            });

        $this->assertHeaderNotHasKey('HTTPS');
    }
}
