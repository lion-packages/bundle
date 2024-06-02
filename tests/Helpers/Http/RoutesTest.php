<?php

declare(strict_types=1);

namespace Tests\Helpers\Http;

use Lion\Bundle\Helpers\Http\Routes;
use Lion\Test\Test;

class RoutesTest extends Test
{
    const MIDDLEWARE = ['app' => [], 'framework' => []];

    private Routes $routes;

    protected function setUp(): void
    {
        $this->routes = new Routes();
    }

    public function testGetMiddleware(): void
    {
        $this->routes->setMiddleware(self::MIDDLEWARE);

        $this->assertSame(self::MIDDLEWARE, $this->routes->getMiddleware());
    }

    public function testSetMiddleware(): void
    {
        $this->assertInstanceOf(Routes::class, $this->routes->setMiddleware(self::MIDDLEWARE));
        $this->assertSame(self::MIDDLEWARE, $this->routes->getMiddleware());
    }
}
