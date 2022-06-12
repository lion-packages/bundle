<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

class LoginController extends Controller {

	public function __construct() {
		$this->init();
	}

    public function auth() {
        return $this->response->success('signin...');
    }

    public function logout() {
        return $this->response->success('logout...');
    }

}