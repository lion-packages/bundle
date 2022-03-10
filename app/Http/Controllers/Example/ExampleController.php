<?php

namespace App\Http\Controllers\Example;

use App\Http\Controllers\Controller;
use LionRoute\Request;
use LionMailer\Mailer;
use LionMailer\Attach;
use Valitron\Validator;
use App\Models\Example\ExampleModel;
use App\Http\Functions\Excel;

class ExampleController extends Controller {

	private ExampleModel $exampleModel;
	
	public function __construct() {
		$this->exampleModel = new ExampleModel();
	}

	public function methodExample(): Request {
		$this->content(true);
		return new Request('success', 'Welcome to example.', self::$form);
	}

}