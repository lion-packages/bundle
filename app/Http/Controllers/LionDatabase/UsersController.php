<?php

namespace App\Http\Controllers\LionDatabase;

use App\Models\LionDatabase\UsersModel;
use Carbon\Carbon;
use Database\Class\LionDatabase\Users;
use LionSecurity\Validation;

class UsersController {

	private UsersModel $usersModel;

	public function __construct() {
		$this->usersModel = new UsersModel();
	}

	public function createUsers() {
		$res_create = $this->usersModel->createUsersDB(
			Users::capsule()
                ->setUsersPassword(Validation::passwordHash(request->users_password))
                ->setUsersCode(uniqid("user-"))
                ->setUsersCreateAt(Carbon::now()->format("Y-m-d H:i:s"))
		);

		return isError($res_create)
			? error($res_create->message)
			: success($res_create->message);
	}

}
