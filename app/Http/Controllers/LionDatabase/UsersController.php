<?php

declare(strict_types=1);

namespace App\Http\Controllers\LionDatabase;

use App\Models\LionDatabase\UsersModel;
use Carbon\Carbon;
use Database\Class\LionDatabase\Users;
use LionSecurity\Validation;

class UsersController
{
	private UsersModel $usersModel;

	public function __construct()
	{
		$this->usersModel = new UsersModel();
	}

	public function createUsers(): array|object
	{
		$code = uniqid("user-");

        $res_create = $this->usersModel->createUsersDB(
            Users::capsule()
                ->setUsersPassword(Validation::passwordHash(request->users_password))
                ->setUsersCode($code)
                ->setUsersCreateAt(Carbon::now()->format("Y-m-d H:i:s"))
        );

        if (isError($res_create)) {
            return error($res_create->message);
        }

        kernel->command("rsa:new -p keys/{$code}/");
        return success($res_create->message);
	}

	public function readUsers(): array|object
	{
		return $this->usersModel->readUsersDB();
	}

	public function updateUsers(): array|object
	{
		$res_update = $this->usersModel->updateUsersDB(
			Users::capsule()
		);

		return isError($res_update)
			? error()
			: success();
	}

	public function deleteUsers(): array|object
	{
		$res_delete = $this->usersModel->deleteUsersDB(
			Users::capsule()
		);

		return isError($res_delete)
			? error()
			: success();
	}
}
