<?php

declare(strict_types=1);

namespace Tests\Middleware;

use Lion\Files\Store;
use Lion\Request\Request;
use Lion\Request\Response;
use Lion\Route\Route;
use Lion\Test\Test;
use Tests\Providers\EnviromentProviderTrait;

class RouteMiddlewareTest extends Test
{
    use EnviromentProviderTrait;

    const URI = 'http://127.0.0.1:8000/route-list';
    const HASH = '0db400cd06201d3ad142a104554f3fb57d712d4524b80cd1d476775b40039a8d';

    private Store $store;

    protected function setUp(): void
    {
        $this->loadEnviroment();

        $this->store = new Store();
    }

    public function testProtectRouteList(): void
    {
        $response = fetch(Route::GET, self::URI, ['headers' => ['Lion-Auth' => env('SERVER_HASH')]])
            ->getBody()
            ->getContents();

        $this->assertJsonStringEqualsJsonFile('./tests/Providers/WebRoutes.json', $response);
    }

    public function testProtectRouteListNotFound1(): void
    {
        $exception = $this->getExceptionFromApi(function() {
            fetch(Route::GET, self::URI)
                ->getBody()
                ->getContents();
        });

        $this->assertJsonContent($this->getResponse($exception->getMessage(), 'response:'), [
            'code' => Request::HTTP_UNAUTHORIZED,
            'status' => Response::SESSION_ERROR,
            'message' => 'Secure hash not found [1]'
        ]);
    }

    public function testProtectRouteListNotAccess(): void
    {
        $exception = $this->getExceptionFromApi(function() {
            fetch(Route::GET, self::URI, ['headers' => ['Lion-Auth' => self::HASH]])
                ->getBody()
                ->getContents();
        });

        $this->assertJsonContent($this->getResponse($exception->getMessage(), 'response:'), [
            'code' => Request::HTTP_UNAUTHORIZED,
            'status' => Response::SESSION_ERROR,
            'message' => 'You do not have access to this resource'
        ]);
    }
}
