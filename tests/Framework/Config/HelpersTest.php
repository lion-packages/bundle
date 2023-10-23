<?php

declare(strict_types=1);

namespace Tests\Framework\Config;

use App\Console\Kernel;
use App\Http\Kernel as HttpKernel;
use GuzzleHttp\Client;
use LionHelpers\Arr;
use LionHelpers\Str;
use LionRequest\Json;
use LionRequest\Response;
use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
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

	public function testJsonConstant(): void
	{
		$this->assertInstanceOf(Json::class, json);
	}

	public function testStrConstant(): void
	{
		$this->assertInstanceOf(Str::class, str);
	}

	public function testArrConstant(): void
	{
		$this->assertInstanceOf(Arr::class, arr);
	}

	public function testKernelConstant(): void
	{
		$this->assertInstanceOf(Kernel::class, kernel);
	}

	public function testFetch(): void
	{
		$response = [
			'code' => 200,
			'status' => 'info',
			'message' => '[index]'
		];

		$this->assertSame($response, fetch('GET', env->SERVER_URL));
	}

	public function testStoragePathForRoot(): void
	{
		$this->assertSame('storage/example/', storage_path('example/', false));
	}

	public function testStoragePathForIndex(): void
	{
		$this->assertSame('../storage/example/', storage_path('example/'));
	}

	public function testSuccess(): void
	{
		$response = success();

		$this->assertIsObject($response);
		$this->assertObjectHasProperty('code', $response);
		$this->assertObjectHasProperty('status', $response);
		$this->assertObjectHasProperty('message', $response);
		$this->assertSame(200, $response->code);
		$this->assertSame('success', $response->status);
		$this->assertSame(null, $response->message);
	}

    public function testResponse(): void
    {
        $response = response();

        $this->assertIsObject($response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertSame(200, $response->code);
        $this->assertSame('custom', $response->status);
        $this->assertSame(null, $response->message);
    }

	public function testError(): void
	{
		$response = error();

		$this->assertIsObject($response);
		$this->assertObjectHasProperty('code', $response);
		$this->assertObjectHasProperty('status', $response);
		$this->assertObjectHasProperty('message', $response);
		$this->assertSame(500, $response->code);
		$this->assertSame('error', $response->status);
		$this->assertSame(null, $response->message);
	}

	public function testWarning(): void
	{
		$response = warning();

		$this->assertIsObject($response);
		$this->assertObjectHasProperty('code', $response);
		$this->assertObjectHasProperty('status', $response);
		$this->assertObjectHasProperty('message', $response);
		$this->assertSame(200, $response->code);
		$this->assertSame('warning', $response->status);
		$this->assertSame(null, $response->message);
	}

	public function testInfo(): void
	{
		$response = info();

		$this->assertIsObject($response);
		$this->assertObjectHasProperty('code', $response);
		$this->assertObjectHasProperty('status', $response);
		$this->assertObjectHasProperty('message', $response);
		$this->assertSame(200, $response->code);
		$this->assertSame('info', $response->status);
		$this->assertSame(null, $response->message);
	}

	public function testJson(): void
	{
		$this->assertJsonStringEqualsJsonString('{"name":"Sleon"}', json([
			'name' => "Sleon"
		]));
	}

	public function testIsError(): void
	{
		$this->assertTrue(isError(error()));
	}

	public function testIsSuccess(): void
	{
		$this->assertTrue(isSuccess(success()));
	}

	public function testSession(): void
	{
		$this->assertInstanceOf(HttpKernel::class, session());
	}

	public function setUp(): void
	{
		require_once(__DIR__ . "/../../../config/helpers.php");
	}
}
