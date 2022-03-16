<?php

namespace App\Http\Functions;

use \OpenSSLAsymmetricKey;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Security {

	private static OpenSSLAsymmetricKey $public_key;
	private static OpenSSLAsymmetricKey $private_key;
	
	public function __construct() {
		
	}

	public static function jwtEncode(array $data): string {
		$payload = [
			'iat' => $time,
			'exp' => ($time + ((60 * 60) * 24)),
			'data' => $data
		];

		return JWT::encode($payload, self::aesEncode($_ENV['AES_IV']), 'RS256');
	}

	public static function jwtDecode(string $jwt) : object {
		return JWT::decode($jwt, new Key(self::aesEncode($_ENV['AES_KEY']), 'RS256'));
	}

	public static function initRSA(): void {
		self::$public_key = openssl_pkey_get_public(
			file_get_contents('app/Server/Secret/public.key')
		);

		self::$private_key = openssl_pkey_get_private(
			file_get_contents('app/Server/Secret/private.key')
		);
	}

	public static function sha256(object $files): object {
		$data_list = [];

		foreach ($files as $key => $file) {
			$data_list[$key] = hash('sha256', $file);
		}

		return (object) $data_list;
	}

	public static function aesEncode(object $files): object {
		$data_list = [];

		foreach ($files as $key => $file) {
			$data_list[$key] = base64_encode(
				openssl_encrypt($file, $_ENV['AES_METHOD'], md5($_ENV['AES_KEY']), OPENSSL_RAW_DATA, $_ENV['AES_IV'])
			);
		}

		return (object) $data_list;
	}

	public static function aesDecode(object $files): object {
		$data_list = [];

		foreach ($files as $key => $file) {
			$data_list[$key] = openssl_decrypt(
				base64_decode($file), $_ENV['AES_METHOD'], md5($_ENV['AES_KEY']), OPENSSL_RAW_DATA, $_ENV['AES_IV']
			);
		}

		return (object) $data_list;
	}

	public static function createKeys(): void {
		$options = [
			'config' => $_ENV['RSA_PATH'],
			'private_key_bits' => $_ENV['RSA_PRIVATE_KEY_BITS'],
			'default_md' => $_ENV['RSA_DEFAULT_MD']
		];

		$generate = openssl_pkey_new($options);
		openssl_pkey_export($generate, $private, null, $options);
		$public = openssl_pkey_get_details($generate);

		file_put_contents("app/Server/Secret/private.key", $private);
		file_put_contents("app/Server/Secret/public.key", $public['key']);
	}

	public static function rsaEncode(object $files): object {
		$data_list = [];

		foreach ($files as $key => $file) {
			openssl_public_encrypt($file, $data, self::$public_key);
			$data_list[$key] = $data;
		}

		return (object) $data_list;
	}

	public static function rsaDecode(object $files): object {
		$data_list = [];

		foreach ($files as $key => $file) {
			openssl_private_decrypt($file, $data, self::$private_key);
			$data_list[$key] = $data;
		}

		return (object) $data_list;
	}

}