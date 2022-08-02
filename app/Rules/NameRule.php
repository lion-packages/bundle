<?php

namespace App\Rules;

use LionSecurity\SECURITY;
use App\Traits\DisplayErrors;

class NameRule {

	use DisplayErrors;

	public function __construct() {

	}

	public function passes(): NameRule {
		$this->validation = SECURITY::validate(
			(array) request, [
                'required', => [
                    ['users_name']
                ],
            ]
		)->data;

		return $this;
	}

}