<?php

namespace App\Rules;

use App\Traits\Framework\ShowErrors;

class ExampleRule {

	use ShowErrors;

	public static function passes(): void {
		self::validate(function(\Valitron\Validator $validator) {
			$validator->rule("required", "example")
                ->message("la propiedad es requerida");

            $validator->rule("alpha", "example")
                ->message("Solo se permiten caracteres alfanumericos");

		});
	}

}