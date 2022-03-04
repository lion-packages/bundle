<?php

namespace App\Http\Middleware;

use LionRoute\Route;
use LionRoute\Request;

class UserAuth {
	
	public function __construct() {
		
	}

	public function auth() {
		if(isset($_SESSION['user_session'])) {
			if (!$_SESSION['user_session']) {
				Route::processOutput(
					new Request("error", "El usuario debe estar autenticado para ver esta página.")
				);
			}
		}
	}

	public function noAuth() {
		if(isset($_SESSION['user_session'])) {
			if ($_SESSION['user_session']) {
				Route::processOutput(
					new Request("error", "El usuario no debe estar autenticado para ver esta página.")
				);
			}
		}
	}

}