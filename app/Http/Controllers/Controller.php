<?php

namespace App\Http\Controllers;

use Valitron\Validator;

class Controller {
	
	public function __construct() {

	}

	public static function make(Validator $validator, array $rules) {
		$validator->rules($rules);
		return $validator->validate();
	}

}