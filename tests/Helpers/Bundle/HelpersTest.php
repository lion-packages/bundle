<?php

declare(strict_types=1);

namespace Tests\Helpers\Bundle;

use LionTest\Test;

class HelpersTest extends Test
{
    const PATH_URL = 'storage/';
    const PATH_URL_INDEX = '../storage/';
    const CUSTOM_FOLDER = 'example/';
    const RESPONSE = ['code' => 200, 'status' => 'info', 'message' => '[index]'];
    const JSON_RESPONSE = '{"name":"Sleon"}';
    const CODE = 'code';
    const STATUS = 'status';
    const MESSAGE = 'message';
    const CUSTOM = 'custom';
    const SUCCESS = 'success';
    const ERROR = 'error';
    const WARNING = 'warning';
    const INFO = 'info';
    const STATUS_200 = 200;
    const STATUS_500 = 500;

	public function testFetch(): void
    {
        $this->assertSame(self::RESPONSE, json_decode(fetch('GET', env->SERVER_URL)->getBody()->getContents(), true));
    }

    public function testStoragePathForRoot(): void
    {
        $this->assertSame(self::PATH_URL . self::CUSTOM_FOLDER, storage_path(self::CUSTOM_FOLDER, false));
    }

    public function testStoragePathForIndex(): void
    {
        $this->assertSame(self::PATH_URL_INDEX . self::CUSTOM_FOLDER, storage_path(self::CUSTOM_FOLDER));
    }

    public function testSuccess(): void
    {
        $response = success();

        $this->assertIsObject($response);
        $this->assertObjectHasProperty(self::CODE, $response);
        $this->assertObjectHasProperty(self::STATUS, $response);
        $this->assertObjectHasProperty(self::MESSAGE, $response);
        $this->assertSame(self::STATUS_200, $response->code);
        $this->assertSame(self::SUCCESS, $response->status);
        $this->assertSame(null, $response->message);
    }

    public function testResponse(): void
    {
        $response = response();

        $this->assertIsObject($response);
        $this->assertObjectHasProperty(self::CODE, $response);
        $this->assertObjectHasProperty(self::STATUS, $response);
        $this->assertObjectHasProperty(self::MESSAGE, $response);
        $this->assertSame(self::STATUS_200, $response->code);
        $this->assertSame(self::CUSTOM, $response->status);
        $this->assertSame(null, $response->message);
    }

    public function testError(): void
    {
        $response = error();

        $this->assertIsObject($response);
        $this->assertObjectHasProperty(self::CODE, $response);
        $this->assertObjectHasProperty(self::STATUS, $response);
        $this->assertObjectHasProperty(self::MESSAGE, $response);
        $this->assertSame(self::STATUS_500, $response->code);
        $this->assertSame(self::ERROR, $response->status);
        $this->assertSame(null, $response->message);
    }

    public function testWarning(): void
    {
        $response = warning();

        $this->assertIsObject($response);
        $this->assertObjectHasProperty(self::CODE, $response);
        $this->assertObjectHasProperty(self::STATUS, $response);
        $this->assertObjectHasProperty(self::MESSAGE, $response);
        $this->assertSame(self::STATUS_200, $response->code);
        $this->assertSame(self::WARNING, $response->status);
        $this->assertSame(null, $response->message);
    }

    public function testInfo(): void
    {
        $response = info();

        $this->assertIsObject($response);
        $this->assertObjectHasProperty(self::CODE, $response);
        $this->assertObjectHasProperty(self::STATUS, $response);
        $this->assertObjectHasProperty(self::MESSAGE, $response);
        $this->assertSame(self::STATUS_200, $response->code);
        $this->assertSame(self::INFO, $response->status);
        $this->assertSame(null, $response->message);
    }

    public function testJson(): void
    {
        $this->assertJsonStringEqualsJsonString(self::JSON_RESPONSE, json(['name' => 'Sleon']));
    }

    public function testIsError(): void
    {
        $this->assertTrue(isError(error()));
        $this->assertFalse(isError(success()));
    }

    public function testIsSuccess(): void
    {
        $this->assertTrue(isSuccess(success()));
        $this->assertFalse(isSuccess(warning()));
    }
}
