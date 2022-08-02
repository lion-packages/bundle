<?php

namespace App\Rules;

use LionSecurity\SECURITY;
use App\Traits\DisplayErrors;

class EmailRule {

	use DisplayErrors;

	public function __construct() {

	}

	public function passes(): EmailRule {
		$this->validation = SECURITY::validate(
			(array) request, [
                'required', => [
                    ['users_email']
                ],
                'email' => [
                    ['users_email']
                ]
            ]
		)->data;

		return $this;
	}

}