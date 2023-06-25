<?php

namespace App\Models\Auth;

use Database\Class\LionDatabase\Users;
use LionSQL\Drivers\MySQL\MySQL as DB;

class LoginModel {

	public function __construct() {

	}

    public function authDB(Users $users) {
        return DB::table('users')
            ->select(DB::as(DB::count('*'), "cont"))
            ->where(DB::equalTo("users_email"), $users->getUsersEmail())
            ->get();
    }

    public function sessionDB(Users $users) {
        return DB::table('users')
            ->select()
            ->where(DB::equalTo("users_email"), $users->getUsersEmail())
            ->get();
    }

}
