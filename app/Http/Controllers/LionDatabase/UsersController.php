<?php

namespace App\Http\Controllers\LionDatabase;

use App\Models\LionDatabase\UsersModel;
use Carbon\Carbon;
use Database\Class\LionDatabase\Users;
use LionSecurity\Validation;

class UsersController {

    private UsersModel $usersModel;

    public function __construct() {
        $this->usersModel = new UsersModel();
    }

    public function createUsers() {
        $code = uniqid("user-");
        $res_create = $this->usersModel->createUsersDB(
            Users::capsule()
                ->setUsersPassword(Validation::passwordHash(request->users_password))
                ->setUsersCode($code)
                ->setUsersCreateAt(Carbon::now()->format("Y-m-d H:i:s"))
        );

        if (isError($res_create)) {
            return error($res_create->message);
        }

        kernel->command("rsa:new -p keys/{$code}/");
        return success($res_create->message);
    }

}
