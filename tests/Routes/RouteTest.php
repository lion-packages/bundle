<?php

declare(strict_types=1);

namespace Tests\Routes;

use LionBundle\Routes\Route;
use LionTest\Test;
use Tests\Providers\ExampleProvider;
use Tests\Providers\HttpMethodsProviderTrait;

class RouteTest extends Test
{
    use HttpMethodsProviderTrait;

    const PREFIX = 'prefix-test';
    const PREFIX_2 = 'prefix-test-2';
    const PREFIX_2_2 = 'prefix-test-2-2';
    const URI = 'test';
    const URI_HTTP = 'test-http';
    const FULL_URI = self::PREFIX . '/' . self::URI;
    const FULL_URI_PREFIX = self::PREFIX . '/' . self::URI_HTTP;
    const FULL_URI_2 = self::PREFIX_2 . '/' . self::PREFIX_2_2 . '/' . self::URI;
    const URI_MATCH = 'match-test';
    const URI_MATCH_2 = 'match-test-2';
    const URI_MATCH_2_2 = 'match-test-22';
    const URI_MATCH_3 = 'match-test-3';
    const PREFIX_MATCH_3 = 'prefix-match-3';
    const FULL_URI_MATCH_3 = self::PREFIX_MATCH_3 . '/' . self::URI_MATCH_3;
    const URI_MATCH_4 = 'match-test-4';
    const PREFIX_MATCH_4 = 'prefix-match-4';
    const FULL_URI_MATCH_4 = self::PREFIX_MATCH_4 . '/' . self::URI_MATCH_4;
    const PREFIX_MIDDLEWARE = 'prefix-middleware';
    const FULL_URI_MIDDLEWARE = self::PREFIX_MIDDLEWARE . '/' . self::URI;
    const PREFIX_MIDDLEWARE_2 = 'prefix-middleware-2';
    const FULL_URI_MIDDLEWARE_2 = self::PREFIX_MIDDLEWARE_2 . '/' . self::URI;
    const ARRAY_RESPONSE = ['isValid' => true];

    private $customClass;

    protected function setUp(): void
    {
        $this->customClass = new class {
            public function exampleMethod1(): void
            {
                echo('TESTING');
            }

            public function exampleMethod2(): void
            {
                echo('TESTING');
            }
        };

        Route::init();
    }

    public function testGetFullRoutes(): void
    {
        $this->assertIsArray(Route::getFullRoutes());
    }

    public function testGetRoutes(): void
    {
        $this->assertIsArray(Route::getRoutes());
    }

    public function testGetFilters(): void
    {
        Route::addMiddleware([$this->customClass::class => self::FILTERS]);

        $filters = Route::getFilters();

        $this->assertIsArray($filters);
        $this->assertArrayHasKey(self::FILTER_NAME_1, $filters);
        $this->assertArrayHasKey(self::FILTER_NAME_2, $filters);
    }

    public function testGetVariables(): void
    {
        $this->assertIsArray(Route::getVariables());
    }

    public function testAddMiddleware(): void
    {
        Route::addMiddleware([$this->customClass::class => self::FILTERS]);

        Route::get('test-add-middleware', fn() => self::ARRAY_RESPONSE, [self::FILTER_NAME_1]);

        $filters = Route::getFilters();

        $this->assertIsArray($filters);
        $this->assertArrayHasKey(self::FILTER_NAME_1, $filters);
        $this->assertArrayHasKey(self::FILTER_NAME_2, $filters);
    }

    /**
     * @dataProvider httpMethodsProvider
     * */
    public function testHttpMethods(string $method, string $httpMethod): void
    {
        Route::$method(self::URI, fn() => self::ARRAY_RESPONSE);

        $fullRoutes = Route::getFullRoutes();

        $this->assertArrayHasKey(self::URI, $fullRoutes);
        $this->assertArrayHasKey($httpMethod, $fullRoutes[self::URI]);
        $this->assertSame(self::ROUTES[$httpMethod], $fullRoutes[self::URI][$httpMethod]);
    }

    /**
     * @dataProvider httpMethodsProvider
     * */
    public function testHttpWithControllersMethods(string $method, string $httpMethod): void
    {
        Route::$method(self::URI, [ExampleProvider::class, 'getArrExample']);

        $fullRoutes = Route::getFullRoutes();

        $this->assertArrayHasKey(self::URI, $fullRoutes);
        $this->assertArrayHasKey($httpMethod, $fullRoutes[self::URI]);
        $this->assertSame(self::ROUTES_CONTROLLER[$httpMethod], $fullRoutes[self::URI][$httpMethod]);
    }

    /**
     * @dataProvider httpMethodsProvider
     * */
    public function testHttpMethodsWithPrefix(string $method, string $httpMethod): void
    {
        Route::prefix(self::PREFIX, function() use ($method) {
            Route::$method(self::URI_HTTP, fn() => self::ARRAY_RESPONSE);
        });

        $fullRoutes = Route::getFullRoutes();

        $this->assertArrayHasKey(self::FULL_URI_PREFIX, $fullRoutes);
        $this->assertArrayHasKey($httpMethod, $fullRoutes[self::FULL_URI_PREFIX]);
        $this->assertSame(self::ROUTES[$httpMethod], $fullRoutes[self::FULL_URI_PREFIX][$httpMethod]);
    }

    /**
     * @dataProvider httpMethodsProvider
     * */
    public function testHttpMethodsWithMiddleware(string $method, string $httpMethod): void
    {
        Route::middleware(self::FILTERS_MIDDLEWARE, function() use ($method) {
            Route::$method(self::URI_HTTP, fn() => self::ARRAY_RESPONSE);
        });

        $fullRoutes = Route::getFullRoutes();

        $this->assertArrayHasKey(self::FULL_URI_PREFIX, $fullRoutes);
        $this->assertArrayHasKey($httpMethod, $fullRoutes[self::FULL_URI_PREFIX]);
        $this->assertSame(self::ROUTES[$httpMethod], $fullRoutes[self::FULL_URI_PREFIX][$httpMethod]);
    }

    public function testMatch(): void
    {
        Route::match([Route::GET, Route::POST], self::URI_MATCH, fn() => self::ARRAY_RESPONSE);

        $fullRoutes = Route::getFullRoutes();

        $this->assertArrayHasKey(self::URI_MATCH, $fullRoutes);
        $this->assertArrayHasKey(Route::GET, $fullRoutes[self::URI_MATCH]);
        $this->assertSame(self::ROUTES[Route::GET], $fullRoutes[self::URI_MATCH][Route::GET]);
        $this->assertArrayHasKey(Route::POST, $fullRoutes[self::URI_MATCH]);
        $this->assertSame(self::ROUTES[Route::POST], $fullRoutes[self::URI_MATCH][Route::POST]);
    }

    public function testMultipleMatch(): void
    {
        Route::match([Route::GET, Route::POST], self::URI_MATCH_2, fn() => self::ARRAY_RESPONSE);
        Route::match([Route::GET, Route::POST, Route::PUT], self::URI_MATCH_2_2, fn() => self::ARRAY_RESPONSE);

        $fullRoutes = Route::getFullRoutes();

        $this->assertArrayHasKey(self::URI_MATCH_2, $fullRoutes);
        $this->assertArrayHasKey(Route::GET, $fullRoutes[self::URI_MATCH_2]);
        $this->assertSame(self::ROUTES[Route::GET], $fullRoutes[self::URI_MATCH_2][Route::GET]);
        $this->assertArrayHasKey(Route::POST, $fullRoutes[self::URI_MATCH_2]);
        $this->assertSame(self::ROUTES[Route::POST], $fullRoutes[self::URI_MATCH_2][Route::POST]);
        $this->assertArrayHasKey(self::URI_MATCH_2_2, $fullRoutes);
        $this->assertArrayHasKey(Route::GET, $fullRoutes[self::URI_MATCH_2_2]);
        $this->assertSame(self::ROUTES[Route::GET], $fullRoutes[self::URI_MATCH_2_2][Route::GET]);
        $this->assertArrayHasKey(Route::POST, $fullRoutes[self::URI_MATCH_2_2]);
        $this->assertSame(self::ROUTES[Route::POST], $fullRoutes[self::URI_MATCH_2_2][Route::POST]);
    }

    public function testMatchWithPrefix(): void
    {
        Route::prefix(self::PREFIX_MATCH_3, function() {
            Route::match([Route::GET, Route::POST], self::URI_MATCH_3, fn() => self::ARRAY_RESPONSE);
        });

        $fullRoutes = Route::getFullRoutes();

        $this->assertArrayHasKey(self::FULL_URI_MATCH_3, $fullRoutes);
        $this->assertArrayHasKey(Route::GET, $fullRoutes[self::FULL_URI_MATCH_3]);
        $this->assertSame(self::ROUTES[Route::GET], $fullRoutes[self::FULL_URI_MATCH_3][Route::GET]);
        $this->assertArrayHasKey(Route::POST, $fullRoutes[self::FULL_URI_MATCH_3]);
        $this->assertSame(self::ROUTES[Route::POST], $fullRoutes[self::FULL_URI_MATCH_3][Route::POST]);
    }

    public function testMatchWithMiddleware(): void
    {
        Route::addMiddleware([$this->customClass::class => self::FILTERS]);

        Route::middleware([self::FILTER_NAME_1, self::FILTER_NAME_2, self::PREFIX_MATCH_4], function() {
            Route::match([Route::GET, Route::POST], self::URI_MATCH_4, fn() => self::ARRAY_RESPONSE);
        });

        $fullRoutes = Route::getFullRoutes();

        $this->assertArrayHasKey(self::FULL_URI_MATCH_4, $fullRoutes);
        $this->assertArrayHasKey(Route::GET, $fullRoutes[self::FULL_URI_MATCH_4]);
        $this->assertSame(self::DATA_METHOD_MIDDLEWARE, $fullRoutes[self::FULL_URI_MATCH_4][Route::GET]);
        $this->assertArrayHasKey(Route::POST, $fullRoutes[self::FULL_URI_MATCH_4]);
        $this->assertSame(self::DATA_METHOD_MIDDLEWARE, $fullRoutes[self::FULL_URI_MATCH_4][Route::POST]);
    }

    public function testPrefix(): void
    {
        Route::prefix(self::PREFIX, function() {
            Route::get(self::URI, fn() => self::ARRAY_RESPONSE);
        });

        $fullRoutes = Route::getFullRoutes();

        $this->assertArrayHasKey(self::FULL_URI, $fullRoutes);
        $this->assertArrayHasKey(Route::GET, $fullRoutes[self::FULL_URI]);
        $this->assertSame(self::ROUTES[Route::GET], $fullRoutes[self::FULL_URI][Route::GET]);
    }

    public function testMultiplePrefix(): void
    {
        Route::prefix(self::PREFIX_2, function() {
            Route::prefix(self::PREFIX_2_2, function() {
                Route::get(self::URI, fn() => self::ARRAY_RESPONSE);
            });
        });

        $fullRoutes = Route::getFullRoutes();

        $this->assertArrayHasKey(self::FULL_URI_2, $fullRoutes);
        $this->assertArrayHasKey(Route::GET, $fullRoutes[self::FULL_URI_2]);
        $this->assertSame(self::ROUTES[Route::GET], $fullRoutes[self::FULL_URI_2][Route::GET]);
    }

    public function testMiddleware(): void
    {
        Route::addMiddleware([$this->customClass::class => self::FILTERS]);

        Route::middleware([self::FILTER_NAME_1, self::FILTER_NAME_2, self::PREFIX_MIDDLEWARE], function() {
            Route::get(self::URI, fn() => self::ARRAY_RESPONSE);
        });

        $fullRoutes = Route::getFullRoutes();

        $this->assertArrayHasKey(self::FULL_URI_MIDDLEWARE, $fullRoutes);
        $this->assertArrayHasKey(Route::GET, $fullRoutes[self::FULL_URI_MIDDLEWARE]);
        $this->assertSame(self::DATA_METHOD_MIDDLEWARE, $fullRoutes[self::FULL_URI_MIDDLEWARE][Route::GET]);
    }

    public function testMultipleMiddleware(): void
    {
        Route::addMiddleware([$this->customClass::class => self::FILTERS]);

        Route::middleware([self::FILTER_NAME_1], function() {
            Route::middleware([self::FILTER_NAME_2], function() {
                Route::prefix(self::PREFIX_MIDDLEWARE_2, function() {
                    Route::get(self::URI, fn() => self::ARRAY_RESPONSE);
                });
            });
        });

        $fullRoutes = Route::getFullRoutes();

        $this->assertArrayHasKey(self::FULL_URI_MIDDLEWARE_2, $fullRoutes);
        $this->assertArrayHasKey(Route::GET, $fullRoutes[self::FULL_URI_MIDDLEWARE_2]);
        $this->assertSame(self::DATA_METHOD_MIDDLEWARE, $fullRoutes[self::FULL_URI_MIDDLEWARE_2][Route::GET]);
    }
}
