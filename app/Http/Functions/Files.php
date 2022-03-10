<?php

namespace App\Http\Functions;

class Files {
	
	public function __construct() {
		
	}

	public static function view(string $path): array {
		$data = [];
		$list = scandir($path, 1);

		for ($i = 0; $i < (count($list) - 2); $i++) {
			array_push($data, "{$path}{$list[$i]}");
		}

		return $data;
	}

	public static function remove(array $files): bool {
		foreach ($files as $key => $file) {
			if (!unlink($file)) {
				return false;
				break;
			}
		}

		return true;
	}

	public static function exist(array $files): bool {
		foreach ($files as $key => $file) {
			if (!file_exists($file)) {
				return false;
				break;
			}
		}

		return true;
	}

	public static function rename(string $file): string {
		return date('Y-m-d') . "-" . md5(hash('sha256', uniqid())) . "." . self::getExtension($file);
	}

	public static function upload(array $tmps, array $names, string $path): bool {
		if (self::folder($path)) {
			foreach ($names as $key_name => $name) {
				if (!move_uploaded_file($tmps[$key_name], $path . self::rename($name))) {
					return false;
					break;
				}
			}
		}

		return true;
	}

	public static function getExtension(string $url_path): string {
		return (new \SplFileInfo($url_path))->getExtension();
	}

	public static function folder(string $path): bool {
		return !self::exist([$path]) ? mkdir($path, 0777, true) : true;
	}

	public static function validate(array $files, array $exts): bool {
		foreach ($files['name'] as $key_file => $file) {
			$file_extension = self::getExtension($file);

			if (!in_array($file_extension, $exts)) {
				return false;
				break;
			}
		}

		return true;
	}

}