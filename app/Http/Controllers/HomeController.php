<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class HomeController extends Controller {

	public function __construct() {
		$this->init();
	}

	public function index() {
		return $this->response->warning('Page not found. [index]');
	}

	public function example($name, $last_name) {
		return $this->response->success("Welcome {$name} {$last_name}");
	}

}