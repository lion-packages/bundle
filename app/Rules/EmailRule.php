<?php

namespace App\Rules;

use App\Traits\Framework\DisplayErrors;

class EmailRule {

	use DisplayErrors;

	public function __construct() {

	}

	public function passes(): EmailRule {
		$this->validateRules([
			'required' => ["users_email"],
			'email' => ["users_email"]
		]);

		return $this;
	}

}