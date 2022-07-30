<?php

namespace App\Http\Controllers\Auth;

class LoginController {

	public function __construct() {

	}

    public function auth() {
        return response->success('Your are loggin!!');
    }

}