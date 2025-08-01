<?php

declare(strict_types=1);

namespace Tests\Helpers\Bundle;

use Carbon\Carbon;
use Carbon\Month;
use Carbon\WeekDay;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Faker\Generator;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Lion\Bundle\Enums\LogTypeEnum;
use Lion\Bundle\Helpers\Env;
use Lion\Bundle\Support\Http\Fetch;
use Lion\Request\Http;
use Lion\Request\Status;
use Lion\Security\AES;
use Lion\Security\JWT;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test as Testing;
use PHPUnit\Framework\Attributes\TestWith;
use stdClass;
use Tests\Providers\Helpers\HelpersProviderTrait;

class HelpersTest extends Test
{
    use HelpersProviderTrait;

    private const string PATH_URL = 'storage/';
    private const string PUBLIC_PATH_URL = 'public/';
    private const string CUSTOM_FOLDER = 'example/';
    private const array RESPONSE = ['code' => Http::OK, 'status' => Status::INFO, 'message' => '[index]'];
    private const string JSON_RESPONSE = '{"name":"Sleon"}';
    private const string CODE = 'code';
    private const string STATUS = 'status';
    private const string MESSAGE = 'message';
    private const string CUSTOM = 'custom';
    private const string LOGGER_CONTENT = 'test-logger';
    private const string USERS_NAME = 'root';

    protected function tearDown(): void
    {
        unset($_SERVER['REQUEST_URI']);
    }

    #[Testing]
    public function getDefaultConnection(): void
    {
        /** @var string $connection */
        $connection = env('DB_DEFAULT');

        $this->assertSame($connection, getDefaultConnection());
    }

    #[Testing]
    public function request(): void
    {
        $_POST['users_name'] = self::USERS_NAME;

        $data = request();

        $this->assertIsObject($data);
        $this->assertInstanceOf(stdClass::class, $data);
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

    #[Testing]
    #[TestWith(['tz' => null])]
    #[TestWith(['tz' => 'UTC'])]
    #[TestWith(['tz' => 'America/Bogota'])]
    #[TestWith(['tz' => new DateTimeZone('America/Bogota')])]
    public function now(DateTimeZone|string|int|null $tz): void
    {
        $this->assertInstanceOf(Carbon::class, now($tz));
    }

    #[Testing]
    #[TestWith(['time' => null, 'timezone' => null])]
    #[TestWith(['time' => '2025-07-10', 'timezone' => null])]
    #[TestWith(['time' => 'now', 'timezone' => null])]
    #[TestWith(['time' => '2025-07-10 15:30:00', 'timezone' => null])]
    #[TestWith(['time' => 'next Monday', 'timezone' => null])]
    #[TestWith(['time' => '2025-07-10T15:30:00+02:00', 'timezone' => null])]
    #[TestWith(['time' => '10 July 2025', 'timezone' => null])]
    #[TestWith(['time' => 1720587600, 'timezone' => null])]
    #[TestWith(['time' => 1720587600.0, 'timezone' => null])]
    #[TestWith(['time' => new DateTime('2025-07-10 12:00:00'), 'timezone' => null])]
    #[TestWith(['time' => WeekDay::Saturday, 'timezone' => null])]
    #[TestWith(['time' => Month::March, 'timezone' => null])]
    #[TestWith(['time' => '2025-07-10', 'timezone' => 'America/Bogota'])]
    #[TestWith(['time' => 'now', 'timezone' => 'America/Bogota'])]
    #[TestWith(['time' => '2025-07-10 15:30:00', 'timezone' => 'America/Bogota'])]
    #[TestWith(['time' => 'next Monday', 'timezone' => 'America/Bogota'])]
    #[TestWith(['time' => '2025-07-10T15:30:00+02:00', 'timezone' => 'America/Bogota'])]
    #[TestWith(['time' => '10 July 2025', 'timezone' => 'America/Bogota'])]
    #[TestWith(['time' => 1720587600, 'timezone' => 'America/Bogota'])]
    #[TestWith(['time' => 1720587600.0, 'timezone' => 'America/Bogota'])]
    #[TestWith(['time' => new DateTime('2025-07-10 12:00:00'), 'timezone' => 'America/Bogota'])]
    #[TestWith(['time' => WeekDay::Saturday, 'timezone' => 'America/Bogota'])]
    #[TestWith(['time' => Month::March, 'timezone' => 'America/Bogota'])]
    #[TestWith(['time' => '2025-07-10', 'timezone' => new DateTimeZone('America/Bogota')])]
    #[TestWith(['time' => 'now', 'timezone' => new DateTimeZone('America/Bogota')])]
    #[TestWith(['time' => '2025-07-10 15:30:00', 'timezone' => new DateTimeZone('America/Bogota')])]
    #[TestWith(['time' => 'next Monday', 'timezone' => new DateTimeZone('America/Bogota')])]
    #[TestWith(['time' => '2025-07-10T15:30:00+02:00', 'timezone' => new DateTimeZone('America/Bogota')])]
    #[TestWith(['time' => '10 July 2025', 'timezone' => new DateTimeZone('America/Bogota')])]
    #[TestWith(['time' => 1720587600, 'timezone' => new DateTimeZone('America/Bogota')])]
    #[TestWith(['time' => 1720587600.0, 'timezone' => new DateTimeZone('America/Bogota')])]
    #[TestWith(['time' => new DateTime('2025-07-10 12:00:00'), 'timezone' => new DateTimeZone('America/Bogota')])]
    #[TestWith(['time' => WeekDay::Saturday, 'timezone' => new DateTimeZone('America/Bogota')])]
    #[TestWith(['time' => Month::March, 'timezone' => new DateTimeZone('America/Bogota')])]
    public function parse(
        DateTimeInterface|WeekDay|Month|string|int|float|null $time,
        DateTimeZone|string|int|null $timezone
    ): void {
        $this->assertInstanceOf(Carbon::class, parse($time, $timezone));
    }

    /**
     * @throws GuzzleException
     */
    public function testFetch(): void
    {
        /** @var string $uri */
        $uri = env('SERVER_URL');

        $fetchResponse = fetch(new Fetch(Http::GET, $uri))
            ->getBody()
            ->getContents();

        $response = json_decode($fetchResponse, true);

        $this->assertSame(self::RESPONSE, $response);
    }

    public function testStoragePathForRoot(): void
    {
        $this->assertSame(self::PATH_URL . self::CUSTOM_FOLDER, storage_path(self::CUSTOM_FOLDER));
    }

    public function testPublicPathForRoot(): void
    {
        $this->assertSame(self::PUBLIC_PATH_URL . self::CUSTOM_FOLDER, public_path(self::CUSTOM_FOLDER));
    }

    public function testResponse(): void
    {
        $response = response();

        $this->assertIsObject($response);
        $this->assertObjectHasProperty(self::CODE, $response);
        $this->assertObjectHasProperty(self::STATUS, $response);
        $this->assertObjectHasProperty(self::MESSAGE, $response);
        $this->assertSame(Http::OK, $response->code);
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
        $this->assertSame(Http::OK, $response->code);
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
        $this->assertSame(Http::INTERNAL_SERVER_ERROR, $response->code);
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
        $this->assertSame(Http::OK, $response->code);
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
        $this->assertSame(Http::OK, $response->code);
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

        $path = storage_path('logs/monolog/');

        $fileName = "{$path}lion-" . now()->format('Y-m-d') . '.log';

        logger(self::LOGGER_CONTENT, LogTypeEnum::INFO, [
            'user' => 'Sleon',
        ]);

        $this->assertFileExists($fileName);
    }

    /**
     * @throws JsonException
     */
    #[Testing]
    public function json(): void
    {
        $this->assertJsonStringEqualsJsonString(self::JSON_RESPONSE, json([
            'name' => 'Sleon',
        ]));
    }

    #[Testing]
    public function jsonError(): void
    {
        $this->expectException(JsonException::class);
        $this->expectExceptionCode(500);

        json(fopen('php://memory', 'r'));
    }

    #[Testing]
    public function isError(): void
    {
        $this->assertTrue(isError(error()));
    }

    #[Testing]
    public function isErrorIsArray(): void
    {
        $this->assertTrue(isError([
            'status' => 'error',
        ]));
    }

    #[Testing]
    public function isErrorObjectNotValid(): void
    {
        $this->assertFalse(isError(new Env()));
    }

    #[Testing]
    public function isErrorStatusNotExists(): void
    {
        $this->assertFalse(isError([]));
    }

    #[Testing]
    public function isSuccess(): void
    {
        $this->assertTrue(isSuccess(success()));
    }

    #[Testing]
    public function isSuccessIsArray(): void
    {
        $this->assertTrue(isSuccess([
            'status' => 'success',
        ]));
    }

    #[Testing]
    public function isSuccessObjectNotValid(): void
    {
        $this->assertFalse(isSuccess(new Env()));
    }

    #[Testing]
    public function isSuccessStatusNotExists(): void
    {
        $this->assertFalse(isSuccess([]));
    }

    public function testJwt(): void
    {
        $config = new AES()
            ->create(AES::AES_256_CBC)
            ->get();

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
