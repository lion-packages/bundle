<?php

namespace App\Http\Controllers;

class Controller {
	
	public function __construct() {
		
	}

	public static function csrf() {
		if (isset($_POST['csrf_token'], $_SESSION["csrf_token"])) {
			if (!empty($_POST["csrf_token"])) {
				if ($_POST['csrf_token'] === $_SESSION["csrf_token"]) {
						// self::processOutput(
						// 	new Request("success")
						// );
				} else {
						// self::processOutput(
						// 	new Request("error", "Invalid Token. [3]")
						// );
				}
			} else {
					// self::processOutput(
					// 	new Request("error", "Invalid Token. [2]")
					// );
			}
		} else {
				// self::processOutput(
				// 	new Request("error", "Invalid Token. [1]")
				// );
		}
	}

}