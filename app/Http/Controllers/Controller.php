<?php

namespace App\Http\Controllers;

use LionMailer\Mailer;
use App\Http\{ Request, Response };
use LionSecurity\RSA;

class Controller {

	protected object $request;
	protected Response $response;

	public function __construct() {

	}

	public function init(): void {
		$this->request = Request::request();
		$this->response = Response::getInstance();
		if ($_ENV['RSA_URL_PATH'] != '') {
			RSA::$url_path = $_ENV['RSA_URL_PATH'];
		}

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

}