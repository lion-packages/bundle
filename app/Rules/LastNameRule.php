<?php

namespace App\Rules;

use LionSecurity\Validation;
use App\Traits\DisplayErrors;

class LastNameRule {

	use DisplayErrors;

	public function __construct() {

	}

	public function passes(): LastNameRule {
		$this->validation = Validation::validate(
			(array) request, [
                'required' => [
                    ['users_last_name']
                ],
            ]
		)->data;

		return $this;
	}

}