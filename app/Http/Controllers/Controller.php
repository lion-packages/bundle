<?php

namespace App\Http\Controllers;

use Valitron\Validator;
use LionMailer\Mailer;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Controller {

	protected static object $form;
	
	public function __construct() {

	}

	public static function content(bool $option = false): void {
		if (!$option) {
			self::$form = (object) ($_POST + $_FILES + $_ENV + $_GET + $_SESSION);
		} else {
			self::$form = (object) json_decode(file_get_contents("php://input"), true);
		}
	}

	public static function make(Validator $validator, array $rules) {
		$validator->rules($rules);
		return $validator->validate();
	}

	public static function init(): void {
		Mailer::init([
			'class' => [
				'PHPMailer' => PHPMailer::class,
				'SMTP' => SMTP::class
			],
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