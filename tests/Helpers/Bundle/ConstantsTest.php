<?php

declare(strict_types=1);

namespace Tests\Helpers\Bundle;

use GuzzleHttp\Client;
use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use Lion\Request\Response;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;

class ConstantsTest extends Test
{
    #[Testing]
    public function clientConstant(): void
    {
        $this->assertTrue(defined('client'));
        $this->assertInstanceOf(Client::class, client);
    }

    #[Testing]
    public function requestConstant(): void
    {
        $this->assertTrue(defined('request'));
        $this->assertIsObject(request);
    }

    #[Testing]
    public function responseConstant(): void
    {
        $this->assertTrue(defined('response'));
        $this->assertInstanceOf(Response::class, response);
    }

    #[Testing]
    public function strConstant(): void
    {
        $this->assertTrue(defined('str'));
        $this->assertInstanceOf(Str::class, str);
    }

    #[Testing]
    public function arrConstant(): void
    {
        $this->assertTrue(defined('arr'));
        $this->assertInstanceOf(Arr::class, arr);
    }

    #[Testing]
    public function nullValueConstant(): void
    {
        $this->assertNull(NULL_VALUE);
    }
}
