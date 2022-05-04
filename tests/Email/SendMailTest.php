<?php

namespace Tests\Email;

use PHPUnit\Framework\TestCase;
use App\Http\Request\Request;
use LionMailer\{ Mailer, Attach };

class SendMailTest extends TestCase {

	public function setUp(): void {
		Mailer::init([
			'info' => [
				'debug' => 0,
				'host' => 'smtp.office365.com',
				'port' => 587,
				'email' => 'example-dev@outlook.com',
				'password' => 'attach-user',
				'user_name' => 'LION FRAMEWORK',
				'encryption' => false
			]
		]);
	}

	public function testSendMailTest() {
		$request = Mailer::send(
			new Attach(
				['sergioleon4004@hotmail.com', 'Sergio León'],
				['example-dev@outlook.com', 'LION FRAMEWORK'],
				null,
				null,
				null,
				'Example testCase',
				'Example testCase',
				'Example testCase'
			)
		);

		$this->assertEquals('success', $request->status);
	}

}