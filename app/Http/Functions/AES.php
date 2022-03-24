<?php

namespace App\Http\Functions;

class AES {

	public function __construct() {

	}

	public static function encode(object $files, string $select_key, string $select_iv): object {
		$data_list = [];

		foreach ($files as $key => $file) {
			$data_list[$key] = base64_encode(
				openssl_encrypt($file, $_ENV['AES_METHOD'], md5($_ENV[$select_key]), OPENSSL_RAW_DATA, $_ENV[$select_iv])
			);
		}

		return (object) $data_list;
	}

	public static function decode(object $files, string $select_key, string $select_iv): object {
		$data_list = [];

		foreach ($files as $key => $file) {
			$data_list[$key] = openssl_decrypt(
				base64_decode($file), $_ENV['AES_METHOD'], md5($_ENV[$select_key]), OPENSSL_RAW_DATA, $_ENV[$select_iv]
			);
		}

		return (object) $data_list;
	}

}