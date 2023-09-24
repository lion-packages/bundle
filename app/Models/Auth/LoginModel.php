<?php

declare(strict_types=1);

namespace App\Models\Auth;

use Database\Class\LionDatabase\Users;
use LionDatabase\Drivers\MySQL\MySQL as DB;

class LoginModel
{
    public function authDB(Users $users): array|object
    {
        return DB::table('users')
            ->select(DB::as(DB::count('*'), "cont"))
            ->where(DB::equalTo("users_email"), $users->getUsersEmail())
            ->get();
    }

    public function sessionDB(Users $users): array|object
    {
        return DB::table('users')
            ->select()
            ->where(DB::equalTo("users_email"), $users->getUsersEmail())
            ->get();
    }
}
