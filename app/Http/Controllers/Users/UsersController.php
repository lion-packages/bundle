<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Users\UsersModel;

class UsersController extends Controller {

	private UsersModel $usersModel;

	public function __construct() {
		$this->init();
		$this->usersModel = new UsersModel();
	}

	public function createUsers(): object {
		return $this->response->success(
			$this->usersModel->createUsersDB($this->request)
		);
	}

	public function readUsers(): array {
		return $this->usersModel->readUsersDB();
	}

}