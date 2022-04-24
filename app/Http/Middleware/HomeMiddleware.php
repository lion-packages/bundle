<?php

namespace App\Http\Middleware;

use App\Http\Middleware\Middleware;

class HomeMiddleware extends Middleware {

	public function __construct() {
		$this->init();
	}

	public function example(): void {
		if (!$this->request->user_session) {
			$this->processOutput(
				$this->response->error('Username does not exist.')
			);
		}
	}

}