<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;

class LoginController extends Controller {
	
	public function __construct() {
		
	}

	public function login() {
		return new Request("success", "Bienvenido: {$_POST['user_data_email']}", $_SESSION);
	}

	public function loginAuth() {
		return new Request("success", "Login Auth exitoso.");
	}
	
}