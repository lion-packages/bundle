<?php

namespace App\Models\LionDatabase;

use Database\Class\LionDatabase\Users;
use LionSQL\Drivers\MySQL\MySQL as DB;
use LionSQL\Drivers\MySQL\Schema;

class UsersModel {

	public function __construct() {
		
	}

	public function createUsersDB(Users $users) {
		return DB::call('create_users', [
			$users->getIdroles(),
			$users->getUsersName(),
			$users->getUsersLastname(),
			$users->getUsersEmail(),
			$users->getUsersPassword(),
			$users->getUsersCode(),
			$users->getUsersCreateAt(),
		])->execute();
	}

}
