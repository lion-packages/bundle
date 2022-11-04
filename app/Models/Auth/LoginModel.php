<?php

namespace App\Models\Auth;

use LionSql\Drivers\MySQLDriver as DB;

class LoginModel {

	public function __construct() {

	}

    public function authDB(): object {
        return DB::table('users')
            ->select(DB::alias(DB::count('*'), "cont"))
            ->where(DB::equalTo("users_email"), request->users_email)
            ->and(DB::equalTo("users_password"), request->users_password)
            ->get();
    }

}