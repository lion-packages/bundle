<?php

namespace App\Models\Auth;

use LionSQL\Drivers\MySQL as DB;

class LoginModel {

	public function __construct() {

	}

    public function authDB() {
        return DB::table('users')
            ->select(DB::as(DB::count('*'), "cont"))
            ->where(DB::equalTo("users_email"), request->users_email)
            ->get();
    }

}