<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;

class ProfileController extends Controller {

	public function __construct() {
		$this->init();
	}

	public function info() {
		return $this->response->success('Authorize', $this->input);
	}

}