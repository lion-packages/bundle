<?php

declare(strict_types=1);

namespace Tests\Framework\Enums;

use App\Enums\Framework\StatusResponseEnum;
use PHPUnit\Framework\TestCase;

class StatusResponseEnumTest extends TestCase 
{
	public function testValues(): void
	{
		$values = [
			'success',
			'error',
			'warning',
			'info',
			'database-error',
			'session-error',
			'route-error',
			'mail-error'
		];

		$this->assertSame($values, StatusResponseEnum::values());
	}

	public function testErrors(): void
	{
		$values = [
			'error',
			'database-error',
			'session-error',
			'route-error',
			'mail-error'
		];

		$this->assertSame($values, StatusResponseEnum::errors());
	}

	public function setUp(): void 
	{

	}
}
