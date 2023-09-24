<?php

declare(strict_types=1);

namespace App\Rules\LionDatabase\Users;

use App\Traits\Framework\ShowErrors;
use Valitron\Validator;

class UsersNameRule
{
	use ShowErrors;

	public static string $field = "users_name";
	public static string $desc = "";
	public static string $value = "";
	public static bool $disabled = false;

	public static function passes(): void
	{
		self::validate(function(Validator $validator) {
			$validator->rule("required", self::$field)->message("property is required");
		});
	}
}