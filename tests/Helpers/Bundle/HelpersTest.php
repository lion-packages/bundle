<?php

declare(strict_types=1);

namespace Tests\Helpers\Bundle;

use Carbon\Carbon;
use DateTimeZone;
use Faker\Generator;
use Lion\Bundle\Enums\LogTypeEnum;
use Lion\Request\Http;
use Lion\Request\Status;
use Lion\Security\AES;
use Lion\Security\JWT;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Providers\EnviromentProviderTrait;
use Tests\Providers\Helpers\HelpersProviderTrait;

class HelpersTest extends Test
{
    use EnviromentProviderTrait;
    use HelpersProviderTrait;

    const PATH_URL = 'storage/';
    const PATH_URL_INDEX = '../storage/';
    const CUSTOM_FOLDER = 'example/';
    const RESPONSE = ['code' => Http::HTTP_OK, 'status' => Status::INFO, 'message' => '[index]'];
    const JSON_RESPONSE = '{"name":"Sleon"}';
    const CODE = 'code';
    const STATUS = 'status';
    const MESSAGE = 'message';
    const CUSTOM = 'custom';
    const LOGGER_CONTENT = 'test-logger';
    const USERS_NAME = 'root';

    protected function setUp(): void
    {
        $this->loadEnviroment();
    }

    protected function tearDown(): void
    {
        unset($_SERVER['REQUEST_URI']);
    }

    public function testRequest(): void
    {
        $_POST['users_name'] = self::USERS_NAME;

        $data = request();

        $this->assertIsObject($data);
        $this->assertObjectHasProperty('users_name', $data);
        $this->assertSame(self::USERS_NAME, $data->users_name);

        unset($_POST['users_name']);

        $this->assertArrayNotHasKey('users_name', $_POST);
    }

    #[DataProvider('requestProvider')]
    public function testRequestWithProperty(string $key, mixed $value, mixed $return): void
    {
        $_POST[$key] = $value;

        $data = request($key);

        $this->assertSame($return, $data);

        unset($_POST[$key]);

        $this->assertArrayNotHasKey($key, $_POST);
    }

    public function testRequestReturnNull(): void
    {
        $data = request(uniqid('code-'));

        $this->assertNull($data);
    }

    public function testNow(): void
    {
        $this->assertInstanceOf(Carbon::class, now());
        $this->assertInstanceOf(Carbon::class, now(new DateTimeZone('America/Bogota')));
    }

    public function testFetch(): void
    {
        $response = json_decode(fetch(Http::HTTP_GET, env('SERVER_URL'))->getBody()->getContents(), true);

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

    public function testFinish(): void
    {
        $this->expectNotToPerformAssertions();
    }

    public function testResponse(): void
    {
        $response = response();

        $this->assertIsObject($response);
        $this->assertObjectHasProperty(self::CODE, $response);
        $this->assertObjectHasProperty(self::STATUS, $response);
        $this->assertObjectHasProperty(self::MESSAGE, $response);
        $this->assertSame(Http::HTTP_OK, $response->code);
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
        $this->assertSame(Http::HTTP_OK, $response->code);
        $this->assertSame(Status::SUCCESS, $response->status);
        $this->assertNull($response->message);
    }

    public function testError(): void
    {
        $response = error();

        $this->assertIsObject($response);
        $this->assertObjectHasProperty(self::CODE, $response);
        $this->assertObjectHasProperty(self::STATUS, $response);
        $this->assertObjectHasProperty(self::MESSAGE, $response);
        $this->assertSame(Http::HTTP_INTERNAL_SERVER_ERROR, $response->code);
        $this->assertSame(Status::ERROR, $response->status);
        $this->assertNull($response->message);
    }

    public function testWarning(): void
    {
        $response = warning();

        $this->assertIsObject($response);
        $this->assertObjectHasProperty(self::CODE, $response);
        $this->assertObjectHasProperty(self::STATUS, $response);
        $this->assertObjectHasProperty(self::MESSAGE, $response);
        $this->assertSame(Http::HTTP_OK, $response->code);
        $this->assertSame(Status::WARNING, $response->status);
        $this->assertNull($response->message);
    }

    public function testInfo(): void
    {
        $response = info();

        $this->assertIsObject($response);
        $this->assertObjectHasProperty(self::CODE, $response);
        $this->assertObjectHasProperty(self::STATUS, $response);
        $this->assertObjectHasProperty(self::MESSAGE, $response);
        $this->assertSame(Http::HTTP_OK, $response->code);
        $this->assertSame(Status::INFO, $response->status);
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

        logger(self::LOGGER_CONTENT, LogTypeEnum::INFO, ['user' => 'Sleon'], false);

        $this->assertFileExists($fileName);
    }

    public function testLoggerForApi(): void
    {
        $_SERVER['REQUEST_URI'] = '/api/test';

        $path = storage_path('logs/monolog/', false);

        $fileName = "{$path}lion-" . Carbon::now()->format('Y-m-d') . '.log';

        logger(self::LOGGER_CONTENT, LogTypeEnum::INFO, ['user' => 'Sleon'], false);

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
        $this->assertFalse(isError(['status' => null]));
        $this->assertFalse(isError(['status' => '']));
        $this->assertFalse(isError(['name' => 'Sleon']));
    }

    public function testIsSuccess(): void
    {
        $this->assertTrue(isSuccess(success()));
        $this->assertFalse(isSuccess(warning()));
        $this->assertFalse(isSuccess(['status' => null]));
        $this->assertFalse(isSuccess(['status' => '']));
        $this->assertFalse(isSuccess(['name' => 'Sleon']));
    }

    public function testJwt(): void
    {
        $config = (new AES())
            ->create(AES::AES_256_CBC)->get();

        $jwt = new JWT();

        $tokenEncode = $jwt
            ->config([
                'privateKey' => $config['iv'],
                'jwtServerUrl' => env('SERVER_URL'),
                'jwtServerUrlAud' => env('SERVER_URL_AUD'),
                'jwtExp' => (int) env('JWT_EXP'),
                'jwtDefaultMD' => 'HS256',
            ])
            ->encode(['session' => true])
            ->get();

        $_SERVER['HTTP_AUTHORIZATION'] = "Bearer {$tokenEncode}";

        $token = jwt();

        $this->assertIsString($token);
    }

    public function testFake(): void
    {
        $this->assertInstanceOf(Generator::class, fake());
    }

    #[DataProvider('envProvider')]
    public function testEnv(string $envKey, mixed $envValue, mixed $return): void
    {
        $this->assertSame($return, env($envKey, $envValue));
    }
}
