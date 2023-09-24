<?php

namespace App\Models\LionDatabase;

use Database\Class\LionDatabase\Users;
use LionDatabase\Drivers\MySQL\MySQL as DB;

class UsersModel
{
	public function createUsersDB(Users $users): array|object
	{
		return DB::call('create_users', [
			$users->getIdroles(),
			$users->getUsersName(),
			$users->getUsersLastName(),
			$users->getUsersEmail(),
			$users->getUsersPassword(),
			$users->getUsersCode(),
			$users->getUsersCreateAt(),
		])->execute();
	}

	public function readUsersDB(): array|object
	{
		return DB::view('read_users')->select()->getAll();
	}

	public function updateUsersDB(Users $users): array|object
	{
		return DB::call('update_users', [
			$users->getIdroles(),
			$users->getUsersName(),
			$users->getUsersLastName(),
			$users->getUsersEmail(),
			$users->getUsersPassword(),
			$users->getUsersCode(),
			$users->getUsersCreateAt(),
			$users->getIdusers(),
		])->execute();
	}

	public function deleteUsersDB(Users $users): array|object
	{
		return DB::call('delete_users', [
			$users->getIdusers(),
		])->execute();
	}
}