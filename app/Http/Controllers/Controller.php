<?php

namespace App\Http\Controllers;

use LionMailer\Mailer;

class Controller {

	protected static object $request;
	
	public function __construct() {

	}

	public static function init(): void {
		Mailer::init([
			'info' => [
				'debug' => $_ENV['MAIL_DEBUG'],
				'host' => $_ENV['MAIL_HOST'],
				'port' => $_ENV['MAIL_PORT'],
				'email' => $_ENV['MAIL_EMAIL'],
				'user_name' => $_ENV['MAIL_USER_NAME'],
				'password' => $_ENV['MAIL_PASSWORD']
			]
		]);
	}

	public static function content(): void {
		$content = json_decode(file_get_contents("php://input"), true);
		self::$request = $content === null ? (object) ($_POST + $_FILES + $_GET) : (object) $content;
	}

}