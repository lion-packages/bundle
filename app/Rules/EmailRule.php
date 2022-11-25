<?php

namespace App\Rules;

use App\Traits\Framework\ShowErrors;

class EmailRule {

	use ShowErrors;

	public static function passes(): void {
		self::validate(function(\Valitron\Validator $validator) {
            $validator->rule("required", "users_email")->message("El correo del usuario es requerido");
            $validator->rule("email", "users_email")->message("El correo del usuario debe ser un correo valido");
        });
	}

}