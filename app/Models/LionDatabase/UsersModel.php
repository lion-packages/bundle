<?php

namespace App\Models\LionDatabase;

use Database\Class\LionDatabase\Users;
use LionDatabase\Drivers\MySQL\MySQL as DB;
use LionDatabase\Drivers\MySQL\Schema;

class UsersModel {

	public function __construct() {
		
	}

	public function createUsersDB(Users $users) {
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

	public function readUsersDB() {
		return DB::view('read_users')->select()->getAll();
	}

	public function updateUsersDB(Users $users) {
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

	public function deleteUsersDB(Users $users) {
		return DB::call('delete_users', [
			$users->getIdusers(),
		])->execute();
	}

}