<?php

namespace App\Rules;

use LionSecurity\Validation;
use App\Traits\DisplayErrors;

class IdentificationDocumentRule {

	use DisplayErrors;

	public function __construct() {

	}

	public function passes(): IdentificationDocumentRule {
		$this->validation = Validation::validate(
			(array) request, [
                'required' => [
                    ['users_identification_document']
                ]
            ]
        )->data;

		return $this;
	}

}