<?php

namespace App\Rules;

use App\Traits\Framework\DisplayErrors;

class PasswordRule {

	use DisplayErrors;

	public function __construct() {

	}

	public function passes(): PasswordRule {
		$this->validateRules([
			'required' => ["users_password"],
		]);

		return $this;
	}

}