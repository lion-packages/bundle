<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use LionRequest\Response;

class RegisterController extends Controller {

	public function __construct() {
		$this->init();
	}

    public function register() {
        return Response::success('signout...');
    }

}