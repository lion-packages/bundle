<?php

namespace App\Http\Middleware\JWT;

use App\Http\Middleware\Middleware;
use LionSecurity\JWT;

class AuthorizationControl extends Middleware {

	public function __construct() {
		$this->init();
	}

	public function exist() {
		$headers = apache_request_headers();

		if (!isset($headers['Authorization'])) {
			$this->processOutput($this->response->error('The JWT does not exist.'));
			exit();
		}
	}

	public function authorize() {
		$headers = apache_request_headers();

		if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
			$jwt = JWT::decode($matches[1]);

			if ($jwt->status === 'error') {
				$this->processOutput($jwt);
				exit();
			}
		} else {
			$this->processOutput($this->response->error('Invalid JWT.'));
			exit();
		}
	}

}