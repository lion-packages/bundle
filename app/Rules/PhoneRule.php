<?php

namespace App\Rules;

use LionSecurity\Validation;
use App\Traits\DisplayErrors;

class PhoneRule {

	use DisplayErrors;

	public function __construct() {

	}

	public function passes(): PhoneRule {
		$this->validation = Validation::validate(
			(array) request, [
                'required' => [
                    ['users_phone']
                ],
            ]
		)->data;

		return $this;
	}

}