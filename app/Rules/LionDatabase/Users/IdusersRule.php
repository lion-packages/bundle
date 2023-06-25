<?php

namespace App\Rules\LionDatabase\Users;

use App\Traits\Framework\ShowErrors;

class IdusersRule {

	use ShowErrors;

	public static string $field = "idusers";
	public static string $desc = "";
	public static string $value = "";
	public static bool $disabled = false;

	public static function passes(): void {
		self::validate(function(\Valitron\Validator $validator) {
			$validator->rule("required", self::$field)->message("property is required");
		});
	}

}