<?php

namespace App\Http\Middleware\Test;

use App\Http\Middleware\Middleware;

class TestMiddleware extends Middleware {

	public function __construct() {
		$this->init();
	}

	public function access(): void {
		if (isset($_SESSION['user_session'])) {
			$this->processOutput(
				$this->response->error('The session does not exist.')
			);
		}
	}

}