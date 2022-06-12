<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;

class UsersController extends Controller {

	public function __construct() {
		$this->init();
	}

	public function createUsers(): object {
		return $this->response->success("user '{$this->request->users_name} {$this->request->users_last_name}' created successfully");
	}

	public function readUsers(): array {
		return [
			(object) [
				'idusers' => 1,
				'users_name' => "Sergio",
				'users_last_name' => "Leon"
			],
			(object) [
				'idusers' => 2,
				'users_name' => "Steve",
				'users_last_name' => "Rogers"
			],
			(object) [
				'idusers' => 3,
				'users_name' => "Peggy",
				'users_last_name' => "Carter"
			],
			(object) [
				'idusers' => 4,
				'users_name' => "Tony",
				'users_last_name' => "Stark"
			],
			(object) [
				'idusers' => 5,
				'users_name' => "Thor",
				'users_last_name' => "Odinson"
			]
		];
	}

}