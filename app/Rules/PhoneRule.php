<?php

namespace App\Rules;

use LionSecurity\SECURITY;
use App\Traits\DisplayErrors;

class PhoneRule {

	use DisplayErrors;

	public function __construct() {

	}

	public function passes(): PhoneRule {
		$this->validation = SECURITY::validate(
			(array) request, [
                'required' => [
                    ['users_phone']
                ],
            ]
		)->data;

		return $this;
	}

}