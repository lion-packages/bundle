<?php

namespace App\Rules;

use LionSecurity\SECURITY;
use App\Traits\DisplayErrors;

class IdentificationDocumentRule {

	use DisplayErrors;

	public function __construct() {

	}

	public function passes(): IdentificationDocumentRule {
		$this->validation = SECURITY::validate(
			(array) request, [
                'required', => [
                    ['users_identification_document']
                ]
            ]
        )->data;

		return $this;
	}

}