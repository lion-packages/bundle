<?php

namespace App\Rules\LionDatabase\Roles;

use App\Traits\Framework\ShowErrors;

class RolesDescriptionRule {

	use ShowErrors;

	public static string $field = "roles_description";
	public static string $desc = "";
	public static string $value = "";
	public static bool $disabled = true;
 
	public static function passes(): void {
		self::validate(function(\Valitron\Validator $validator) {
			$validator->rule("optional", self::$field)->message("property is optional");
		});
	}

}