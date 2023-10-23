<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Models\Auth\LoginModel;
use Database\Class\LionDatabase\Users;
use LionFiles\Store;
use LionSecurity\JWT;
use LionSecurity\RSA;

class LoginController
{
    private LoginModel $loginModel;

	public function __construct()
    {
        $this->loginModel = new LoginModel();
	}

    public function auth(): array|object
    {
        $users = Users::capsule();

        $cont = $this->loginModel->authDB($users);
        if ($cont->cont === 0) {
            return error("email/password is invalid");
        }

        $session = $this->loginModel->sessionDB($users);
        if (!password_verify($users->getUsersPassword(), $session->users_password)) {
            return error("email/password is invalid");
        }

        $path = storage_path("keys/{$session->users_code}/");
        if (isError(Store::exist($path))) {
            return error("the keys do not exist");
        }

        RSA::setPath($path);
        return success("Welcome: {$session->users_name} {$session->users_last_name}", 200, [
            'jwt' => JWT::encode([
                'session' => true,
                'idusers' => $session->idusers,
                'idroles' => $session->idroles,
                'users_code' => $session->users_code
            ])
        ]);
    }
}
