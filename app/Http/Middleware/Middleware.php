<?php

namespace App\Http\Middleware;

use App\Http\Response;
use LionSecurity\RSA;

class Middleware {

	protected Response $response;

	public function __construct() {

	}

	public function init(): void {
		$this->response = Response::getInstance();
		if ($_ENV['RSA_URL_PATH'] != '') {
			RSA::$url_path = $_ENV['RSA_URL_PATH'];
		}
	}

	public function processOutput($response): void {
		echo(json_encode($response));
	}

}