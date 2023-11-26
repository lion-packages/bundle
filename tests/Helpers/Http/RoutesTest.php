<?php

declare(strict_types=1);

namespace Tests\Helpers\Http;

use LionBundle\Helpers\Http\Routes;
use LionTest\Test;

class RoutesTest extends Test
{
    const RULES = ['POST' => []];
    const MIDDLEWARE = ['app' => [], 'framework' => []];

    public function testGetRules(): void
    {
        Routes::setRules(self::RULES);

        $this->assertSame(self::RULES, Routes::getRules());
    }

    public function testGetMiddleware(): void
    {
        Routes::setMiddleware(self::MIDDLEWARE);

        $this->assertSame(self::MIDDLEWARE, Routes::getMiddleware());
    }
}
