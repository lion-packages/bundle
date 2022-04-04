<?php

namespace App\Http\Controllers;

use LionMailer\Mailer;
use App\Models\Class\Request;

class Controller {

	protected Request $request;
	protected object $input;

	public function __construct() {

	}

	public function content(): void {
		$content = json_decode(file_get_contents("php://input"), true);
		$this->input = $content === null ? (object) ($_POST + $_FILES + $_GET) : (object) $content;
	}

	public function init(): void {
		$this->content();
		$this->request = Request::getInstance();
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