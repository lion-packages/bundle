<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class HomeController extends Controller {

	public function __construct() {
		$this->init();
	}

	public function index(): array {
		return $this->request->request('warning', 'Page not found. [index]');
	}

}