<?php

namespace App\Traits;

trait Singleton {

	private array $data_constructor;
	private static $singleton = false;

	final private function __construct(array $data_constructor) {
		$this->data_constructor = $data_constructor;
		$this->init();
	}

	final public static function getInstance(array $data_constructor = []) {
		if (self::$singleton === false) {
			self::$singleton = new self($data_constructor);
		}

		return self::$singleton;
	}

	private function init(): void {}

}