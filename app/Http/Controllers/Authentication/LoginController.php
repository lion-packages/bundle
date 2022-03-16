<?php

namespace App\Http\Controllers\Authentication;

use App\Http\Controllers\Controller;

class LoginController extends Controller {

	public function __construct() {

	}

	public function loginAuth() {
		return ['message' => 'hello world'];
	}

}