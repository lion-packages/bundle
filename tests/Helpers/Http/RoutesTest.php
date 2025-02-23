<?php

declare(strict_types=1);

namespace Tests\Helpers\Http;

use Lion\Bundle\Helpers\Http\Routes;
use Lion\Bundle\Middleware\RouteMiddleware;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;

class RoutesTest extends Test
{
    /**
     * @var array<string, class-string>
     */
    private array $middelware;

    private Routes $routes;

    protected function setUp(): void
    {
        $this->routes = new Routes();

        $this->middelware = [
            'protect-route-list' => RouteMiddleware::class,
        ];
    }

    #[Testing]
    public function getMiddleware(): void
    {
        $this->routes->setMiddleware($this->middelware);

        $this->assertSame($this->middelware, $this->routes->getMiddleware());
    }

    #[Testing]
    public function setMiddleware(): void
    {
        $this->routes->setMiddleware($this->middelware);

        $this->assertSame($this->middelware, $this->routes->getMiddleware());
    }
}
