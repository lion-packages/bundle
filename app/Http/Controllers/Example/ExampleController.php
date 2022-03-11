<?php

namespace App\Http\Controllers\Example;

use App\Http\Controllers\Controller;
use LionRoute\Request;
use LionMailer\Mailer;
use LionMailer\Attach;
use Valitron\Validator;
use App\Models\Example\ExampleModel;
use App\Http\Functions\Word;
use App\Http\Functions\Pdf;
use App\Http\Functions\Files;

class ExampleController extends Controller {

	private ExampleModel $exampleModel;
	
	public function __construct() {
		$this->exampleModel = new ExampleModel();
	}

	public function methodExample(): Request {
		$this->content(true);
		return new Request('success', 'Welcome to example.', self::$form);
	}

	public function createMyWord() {
		$this->content(true);
		
		Word::loadTemplate('public/template/template.docx');
		Word::add([
			'user_data_email' => self::$form->user_data_email,
			'user_data_password' => self::$form->user_data_password
		]);

		$word_file = Word::saveTemplate('public/', 'nuevo_data', true);	

		return new Request('success', 'documento creado.');
	}

}