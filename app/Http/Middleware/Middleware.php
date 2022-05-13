<?php

namespace App\Http\Middleware;

use App\Http\Request\{ Request, Json, Response };
use LionSecurity\RSA;

class Middleware {

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
		RSA::$url_path = $this->env->RSA_URL_PATH === '' ? RSA::$url_path : $this->env->RSA_URL_PATH;
	}

	public function processOutput($response): void {
		echo(json_encode($response));
		exit();
	}

}