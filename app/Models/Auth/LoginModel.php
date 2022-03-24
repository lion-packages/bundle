<?php

namespace App\Models\Auth;

use App\Models\Model;
use LionSql\QueryBuilder as Builder;
use App\Models\Class\Login;

class LoginModel extends Model {

	public function __construct() {
		$this->init();
	}

	public function validateAccount(Login $login): array {
		return Builder::select('fetch', 'validate_login', null, 'users_password', [
			Builder::where('users_email', '=')
		], [
			[$login->getUsersEmail()]
		]);
	}

	public function readUserDataDB(Login $login): array {
		return Builder::select('fetch', 'users', null, 'idusers', [
			Builder::where('users_email', '=')
		], [
			[$login->getUsersEmail()]
		]);
	}

}