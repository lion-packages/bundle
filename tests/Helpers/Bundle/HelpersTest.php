<?php

declare(strict_types=1);

namespace Tests\Helpers\Bundle;

use Carbon\Carbon;
use Faker\Generator;
use Lion\Bundle\Enums\LogTypeEnum;
use Lion\Request\Request;
use Lion\Request\Response;
use Lion\Route\Route;
use Lion\Test\Test;
use Tests\Providers\EnviromentProviderTrait;

class HelpersTest extends Test
{
    use EnviromentProviderTrait;

    const PATH_URL = 'storage/';
    const PATH_URL_INDEX = '../storage/';
    const CUSTOM_FOLDER = 'example/';
    const RESPONSE = ['code' => Request::HTTP_OK, 'status' => Response::INFO, 'message' => '[index]'];
    const JSON_RESPONSE = '{"name":"Sleon"}';
    const CODE = 'code';
    const STATUS = 'status';
    const MESSAGE = 'message';
    const CUSTOM = 'custom';
    const LOGGER_CONTENT = 'test-logger';

    protected function setUp(): void
    {
        $this->loadEnviroment();
    }

	public function testFetch(): void
    {
        $response = json_decode(fetch(Route::GET, $_ENV['SERVER_URL'])->getBody()->getContents(), true);

        $this->assertSame(self::RESPONSE, $response);
    }

    public function testStoragePathForRoot(): void
    {
        $this->assertSame(self::PATH_URL . self::CUSTOM_FOLDER, storage_path(self::CUSTOM_FOLDER, false));
    }

    public function testStoragePathForIndex(): void
    {
        $this->assertSame(self::PATH_URL_INDEX . self::CUSTOM_FOLDER, storage_path(self::CUSTOM_FOLDER));
    }

    public function testResponse(): void
    {
        $response = response();

        $this->assertIsObject($response);
        $this->assertObjectHasProperty(self::CODE, $response);
        $this->assertObjectHasProperty(self::STATUS, $response);
        $this->assertObjectHasProperty(self::MESSAGE, $response);
        $this->assertSame(Request::HTTP_OK, $response->code);
        $this->assertSame(self::CUSTOM, $response->status);
        $this->assertNull($response->message);
    }

    public function testSuccess(): void
    {
        $response = success();

        $this->assertIsObject($response);
        $this->assertObjectHasProperty(self::CODE, $response);
        $this->assertObjectHasProperty(self::STATUS, $response);
        $this->assertObjectHasProperty(self::MESSAGE, $response);
        $this->assertSame(Request::HTTP_OK, $response->code);
        $this->assertSame(Response::SUCCESS, $response->status);
        $this->assertNull($response->message);
    }

    public function testError(): void
    {
        $response = error();

        $this->assertIsObject($response);
        $this->assertObjectHasProperty(self::CODE, $response);
        $this->assertObjectHasProperty(self::STATUS, $response);
        $this->assertObjectHasProperty(self::MESSAGE, $response);
        $this->assertSame(Request::HTTP_INTERNAL_SERVER_ERROR, $response->code);
        $this->assertSame(Response::ERROR, $response->status);
        $this->assertNull($response->message);
    }

    public function testWarning(): void
    {
        $response = warning();

        $this->assertIsObject($response);
        $this->assertObjectHasProperty(self::CODE, $response);
        $this->assertObjectHasProperty(self::STATUS, $response);
        $this->assertObjectHasProperty(self::MESSAGE, $response);
        $this->assertSame(Request::HTTP_OK, $response->code);
        $this->assertSame(Response::WARNING, $response->status);
        $this->assertNull($response->message);
    }

    public function testInfo(): void
    {
        $response = info();

        $this->assertIsObject($response);
        $this->assertObjectHasProperty(self::CODE, $response);
        $this->assertObjectHasProperty(self::STATUS, $response);
        $this->assertObjectHasProperty(self::MESSAGE, $response);
        $this->assertSame(Request::HTTP_OK, $response->code);
        $this->assertSame(Response::INFO, $response->status);
        $this->assertNull($response->message);
    }

    public function testVd(): void
    {
        ob_start();
        vd('Testing');
        $output = ob_get_clean();

        $this->assertStringContainsString('Testing', $output);
    }

    public function testLogger(): void
    {
        $path = storage_path('logs/monolog/', false);
        $fileName = "{$path}lion-" . Carbon::now()->format('Y-m-d') . '.log';
        logger(self::LOGGER_CONTENT, LogTypeEnum::INFO->value, ['user' => 'Sleon'], false);

        $this->assertFileExists($fileName);
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

    public function testFake(): void
    {
        $this->assertInstanceOf(Generator::class, fake());
    }
}
