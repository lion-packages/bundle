<?php

declare(strict_types=1);

namespace Tests\Middleware;

use Lion\Bundle\Exceptions\MiddlewareException;
use Lion\Bundle\Middleware\RouteMiddleware;
use Lion\Exceptions\Exception;
use Lion\Request\Http;
use Lion\Request\Status;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;

class RouteMiddlewareTest extends Test
{
    private RouteMiddleware $routeMiddleware;

    protected function setUp(): void
    {
        $this->routeMiddleware = new RouteMiddleware();
    }

    /**
     * @throws Exception
     */
    #[Testing]
    public function protectRouteListWithoutHeader(): void
    {
        $this
            ->exception(MiddlewareException::class)
            ->exceptionMessage('secure hash not found')
            ->exceptionStatus(Status::SESSION_ERROR)
            ->exceptionCode(Http::UNAUTHORIZED)
            ->expectLionException(function (): void {
                $this->routeMiddleware->protectRouteList();
            });
    }

    /**
     * @throws Exception
     */
    #[Testing]
    public function protectedRouteListDiferentHash(): void
    {
        $this
            ->exception(MiddlewareException::class)
            ->exceptionMessage('you do not have access to this resource')
            ->exceptionStatus(Status::SESSION_ERROR)
            ->exceptionCode(Http::UNAUTHORIZED)
            ->expectLionException(function (): void {
                $_SERVER['HTTP_LION_AUTH'] = 'ff1d1bcda9afa5873bdc8205c11e880a43351ea56dc059f6544116961f6f5c0e';

                $this->routeMiddleware->protectRouteList();
            });
    }
}
