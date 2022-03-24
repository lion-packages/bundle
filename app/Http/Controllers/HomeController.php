<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Class\Request;

class HomeController extends Controller {

	public function __construct() {

	}

	public function index(): Request {
		return new Request('warning', 'Page not found. [index]');
	}

}