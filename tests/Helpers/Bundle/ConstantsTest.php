<?php

declare(strict_types=1);

namespace Tests\Helpers\Bundle;

use GuzzleHttp\Client;
use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use Lion\Request\Response;
use Lion\Test\Test;

class ConstantsTest extends Test
{
    public function testClientConstant(): void
    {
        $this->assertTrue(defined('client'));
        $this->assertInstanceOf(Client::class, client);
    }

    public function testRequestConstant(): void
    {
        $this->assertTrue(defined('request'));
        $this->assertIsObject(request);
    }

    public function testResponseConstant(): void
    {
        $this->assertTrue(defined('response'));
        $this->assertInstanceOf(Response::class, response);
    }

    public function testStrConstant(): void
    {
        $this->assertTrue(defined('str'));
        $this->assertInstanceOf(Str::class, str);
    }

    public function testArrConstant(): void
    {
        $this->assertTrue(defined('arr'));
        $this->assertInstanceOf(Arr::class, arr);
    }
}
