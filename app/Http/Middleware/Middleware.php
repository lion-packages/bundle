<?php

namespace App\Http\Middleware;

use App\Http\Response;

class Middleware {

	protected Response $response;

	public function __construct() {

	}

	public function init(): void {
		$this->response = Response::getInstance();
	}

	public function processOutput($response): void {
		echo(json_encode($response));
	}

}