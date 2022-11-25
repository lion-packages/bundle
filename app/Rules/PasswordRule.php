<?php

namespace App\Rules;

use App\Traits\Framework\ShowErrors;

class PasswordRule {

	use ShowErrors;

	public static function passes(): void {
		self::validate(function(\Valitron\Validator $validator) {
			$validator->rule("required", "users_password")->message("La contraseña del usuario es requerida");
		});
	}

}