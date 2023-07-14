<?php

namespace App\Http\Controllers\Auth;

use App\Models\Auth\LoginModel;
use Database\Class\LionDatabase\Users;
use LionFiles\Store;
use LionSecurity\JWT;
use LionSecurity\RSA;

class LoginController {

    private LoginModel $loginModel;

	public function __construct() {
        $this->loginModel = new LoginModel();
	}

    public function auth() {
        $users = Users::capsule();

        $cont = $this->loginModel->authDB($users);
        if ($cont->cont === 0) {
            return error(500, "email/password is invalid");
        }

        $session = $this->loginModel->sessionDB($users);
        if (!password_verify($users->getUsersPassword(), $session->users_password)) {
            return error(500, "email/password is invalid");
        }

        $path = storage_path("keys/{$session->users_code}/");
        if (isError(Store::exist($path))) {
            return error(500, "the keys do not exist");
        }

        RSA::$url_path = $path;
        return success(200, "Welcome: {$session->users_name} {$session->users_last_name}", [
            'jwt' => JWT::encode([
                'session' => true,
                'idusers' => $session->idusers,
                'idroles' => $session->idroles
            ])
        ]);
    }

}
