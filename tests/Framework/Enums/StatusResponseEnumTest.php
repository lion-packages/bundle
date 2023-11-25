<?php

declare(strict_types=1);

namespace Tests\Framework\Enums;

use LionBundle\Enums\StatusResponseEnum;
use LionTest\Test;

class StatusResponseEnumTest extends Test
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
}
