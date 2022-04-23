<?php

namespace App\Traits;

trait ValidateClass {

	public static function validate(string $class, string $method): array {
		$list_validator = [
			'controller' => [
				'method' => [
					'rule' => []
				]
			]
		];

		return isset($list_validator[$class][$method]) ? $list_validator[$class][$method] : [];
	}

}