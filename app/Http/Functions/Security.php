<?php

namespace App\Http\Functions;

use Valitron\Validator;

class Security {
	
	public function __construct() {
		
	}

	public static function passwordVerify(string $password, string $confirm_password): bool {
		return password_verify($password, $confirm_password);
	}

	public static function passwordHash(string $file): string {
		return password_hash($file, PASSWORD_BCRYPT, [
			'cost' => 10
		]);
	}

	public static function validate(array $files, array $rules) {
		$validator = new Validator($files);
		$validator->rules($rules);
		return $validator->validate();
	}

	public static function sha256(object $files): object {
		$data_list = [];

		foreach ($files as $key => $file) {
			$data_list[$key] = hash('sha256', $file);
		}

		return (object) $data_list;
	}

}