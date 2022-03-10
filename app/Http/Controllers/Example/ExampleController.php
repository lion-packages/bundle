<?php

namespace App\Http\Controllers\Example;

use App\Http\Controllers\Controller;
use LionRoute\Request;
use LionMailer\Mailer;
use LionMailer\Attach;
use Valitron\Validator;
use App\Models\Class\Example;

class ExampleController extends Controller {
	
	public function __construct() {
		
	}

	public function methodExample(): Request {
		$this->content(true);

		$validator = $this->make(new Validator((array) self::$form), [
			'email' => [
				['user_data_email']
			],
			'required' => [
				['user_data_email'], 
				['user_data_password']
			]
		]);

		if($validator) {
			return new Request('success', 'Welcome to example.', self::$form);
		} else {
			return new Request('error', 'All fields are required and must meet the requested characteristics.');
		}
	}

}