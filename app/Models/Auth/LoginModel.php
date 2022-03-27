<?php

namespace App\Models\Auth;

use App\Models\Model;
use LionSql\QueryBuilder as Builder;
use App\Models\Class\Users;

class LoginModel extends Model {

	public function __construct() {
		$this->init();
	}

	public function validateAccount(Users $user): array {
		return Builder::select('fetch', 'validate_login', null, 'users_password', [
			Builder::where('users_email', '=')
		], [
			[$user->getUsersEmail()]
		]);
	}

	public function readUserDataDB(Users $user): array {
		return Builder::select('fetch', 'users', null, 'idusers', [
			Builder::where('users_email', '=')
		], [
			[$user->getUsersEmail()]
		]);
	}

}