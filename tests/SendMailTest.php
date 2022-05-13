<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Dotenv\Dotenv;
use LionMailer\Mailer;
use LionMailer\DataMailer\Attach;
use App\Http\Request\Request;

class SendMailTest extends TestCase {

	public function setUp() {
		(Dotenv::createImmutable(__DIR__))->load();

		Mailer::init([
			'info' => [
				'debug' => (int) $_ENV['MAIL_DEBUG'],
				'host' => $_ENV['MAIL_HOST'],
				'port' => (int) $_ENV['MAIL_PORT'],
				'email' => $_ENV['MAIL_EMAIL'],
				'password' => $_ENV['MAIL_PASSWORD'],
				'user_name' => $_ENV['MAIL_USER_NAME'],
				'encryption' => $_ENV['MAIL_ENCRYPTION'] === 'false' ? false : ($_ENV['MAIL_ENCRYPTION'] === 'true' ? true : false)
			]
		]);
	}

	public function testSendMailTest() {
		$request = Mailer::send(
			Attach::newAttach(
				[$_ENV['MAIL_SEND_MAIL'], $_ENV['MAIL_USER_NAME']],
				[$_ENV['MAIL_EMAIL'], $_ENV['MAIL_USER_NAME']],
				null,
				null
			),
			Mailer::newInfo(
				'example',
				'example',
				'example'
			)
		);

		$this->assertEquals('success', $request->status);
	}

}