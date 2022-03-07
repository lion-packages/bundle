<?php

namespace App\Http\Controllers\Example;

use App\Http\Controllers\Controller;
use LionRoute\Request;
use Valitron\Validator;
use App\Models\Class\Example;

class ExampleController extends Controller {
	
	public function __construct() {

	}

	public function methodExample(): Request {
		$validator = $this->make(new Validator($_POST), [
			'email' => [
				['user_data_email']
			],
			'lengthMin' => [
				['user_data_password', 8]
			],
			'required' => [
				['user_data_email'], 
				['user_data_password']
			]
		]);

		if($validator) {
			return new Request('success', 'Welcome to example.', $_POST);
		} else {
			return new Request('error', 'All fields are required and must meet the requested characteristics.');
		}
	}

	public function methodToken(): array {
		return [
			'status' => "warning",
			'message' => "Welcome to token",
			'data' => $_POST
		];
	}

}