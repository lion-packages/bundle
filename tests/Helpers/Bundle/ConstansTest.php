<?php

declare(strict_types=1);

namespace Tests\Helpers\Bundle;

use GuzzleHttp\Client;
use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use Lion\Request\Response;
use Lion\Test\Test;

class ConstansTest extends Test
{
    public function testClientConstant(): void
    {
        $this->assertInstanceOf(Client::class, client);
    }

    public function testRequestConstant(): void
    {
        $this->assertIsObject(request);
    }

    public function testResponseConstant(): void
    {
        $this->assertInstanceOf(Response::class, response);
    }

    public function testStrConstant(): void
    {
        $this->assertInstanceOf(Str::class, str);
    }

    public function testArrConstant(): void
    {
        $this->assertInstanceOf(Arr::class, arr);
    }
}
