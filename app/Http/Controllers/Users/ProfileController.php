<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Class\Request;

class ProfileController extends Controller {

	public function __construct() {

	}

	public function info() {
		return new Request('success', 'Authorize', [
			'info' => self::$request
		]);
	}

}