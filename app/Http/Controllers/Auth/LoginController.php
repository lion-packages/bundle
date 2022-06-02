<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use LionRequest\Response;

class LoginController extends Controller {

	public function __construct() {
		$this->init();
	}

    public function auth() {
        return Response::success('signin...');
    }

    public function logout() {
        return Response::success('logout...');
    }

}