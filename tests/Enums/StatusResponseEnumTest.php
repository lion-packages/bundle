<?php

declare(strict_types=1);

namespace Tests\Enums;

use Lion\Bundle\Enums\StatusResponseEnum;
use Lion\Request\Response;
use Lion\Test\Test;

class StatusResponseEnumTest extends Test
{
	public function testValues(): void
	{
		$values = [
			Response::SUCCESS,
			Response::ERROR,
			Response::WARNING,
			Response::INFO,
			Response::DATABASE_ERROR,
			Response::SESSION_ERROR,
			Response::ROUTE_ERROR,
            Response::FILE_ERROR,
			Response::MAIL_ERROR
		];

		$this->assertSame($values, StatusResponseEnum::values());
	}

	public function testErrors(): void
	{
		$this->assertSame((new Response)->getErrors(), StatusResponseEnum::errors());
	}
}
