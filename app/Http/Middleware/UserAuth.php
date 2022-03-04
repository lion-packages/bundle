<?php

namespace App\Http\Middleware;

use App\Config\PHPRoute;
use App\Models\Class\Request;

class UserAuth {
	
	public function __construct() {
		
	}

	public function auth() {
		if(isset($_SESSION['user_session'])) {
			if (!$_SESSION['user_session']) {
				PHPRoute::processOutput(
					new Request("error", "El usuario debe estar autenticado para ver esta página.")
				);
			}
		}
	}

	public function noAuth() {
		if(isset($_SESSION['user_session'])) {
			if ($_SESSION['user_session']) {
				PHPRoute::processOutput(
					new Request("error", "El usuario no debe estar autenticado para ver esta página.")
				);
			}
		}
	}

}