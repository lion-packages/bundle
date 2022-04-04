<?php

namespace App\Http\Middleware;

use LionRoute\Route;
use LionSecurity\JWT;
use App\Models\Class\Request;

class AuthorizeJWT {

	private Request $request;

	public function __construct() {
		$this->request = Request::getInstance();
	}

	public function existJWT() {
		$headers = apache_request_headers();

		if (!isset($headers['Authorization'])) {
			Route::processOutput($this->request->request('error', 'The JWT does not exist.'));
			exit();
		}
	}

	public function authorizeJWT() {
		$headers = apache_request_headers();

		if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
			$jwt = JWT::decode($matches[1]);

			if ($jwt->status === 'error') {
				Route::processOutput($jwt);
				exit();
			}
		} else {
			Route::processOutput($this->request->request('error', 'Invalid JWT.'));
			exit();
		}
	}

}