<?php

namespace App\Http\Controllers\Example;

use App\Http\Controllers\Controller;

class ExampleController extends Controller {
	
	public function __construct() {
		
	}

	public function methodExample() {
		return [
			'status' => "success",
			'message' => "Welcome to example"
		];
	}

}