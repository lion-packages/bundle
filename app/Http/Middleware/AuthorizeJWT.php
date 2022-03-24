<?php

namespace App\Http\Middleware;

use LionRoute\Route;
use App\Models\Class\Request;
use App\Http\Functions\JWT;

class AuthorizeJWT {

	public function __construct() {

	}

	public function existJWT() {
		$headers = apache_request_headers();

		if (!isset($headers['Authorization'])) {
			Route::processOutput(new Request('error', 'The JWT does not exist.'));
			exit();
		}
	}

	public function authorizeJWT() {
		$headers = apache_request_headers();

		if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
			JWT::decode($matches[1]);
		} else {
			Route::processOutput(new Request('error', 'Bad JWT.'));
			exit();
		}
	}

}