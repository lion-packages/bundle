<?php

namespace App\Http\Controllers;

use LionMailer\Mailer;
use App\Http\Request\{ Request, Json, Response };
use LionSecurity\RSA;

class Controller {

	protected object $request;
	protected object $env;
	protected Json $json;
	protected Response $response;

	public function __construct() {

	}

	public function init(): void {
		$this->env = Request::getInstance()->env();
		$this->request = Request::getInstance()->request();
		$this->json = Json::getInstance();
		$this->response = Response::getInstance();

		if ($this->env->RSA_URL_PATH != '') {
			RSA::$url_path = $this->env->RSA_URL_PATH;
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