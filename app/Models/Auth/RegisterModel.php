<?php

namespace App\Models\Auth;

use App\Models\Model;
use LionSql\QueryBuilder as Builder;
use App\Models\Class\Users;

class RegisterModel extends Model {

	public function __construct() {
		$this->init();
	}

	public function validateUserExistenceDB(string $column, string $value) {
		return Builder::select('fetch', 'users', null, Builder::count(null, 'files'), [
			Builder::where($column, '=')
		], [
			[$value, ($column === 'users_email' ? 'str' : 'int')]
		]);
	}

	public function createUserDB(Users $users): array {
		return Builder::call('create_user', [
			[$users->getUsersEmail()],
			[$users->getUsersPassword()],
			[$users->getUsersName()],
			[$users->getUsersLastName()],
			[$users->getUsersDocument(), 'int'],
			[$users->getDocumentTypes()->getIddocumentTypes(), 'int'],
			[$users->getUsersPhone(), 'int']
		]);
	}

}