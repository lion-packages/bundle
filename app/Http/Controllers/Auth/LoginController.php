<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Functions\{ Security, AES, JWT, RSA };
use App\Models\Class\{ Request, Login };
use App\Models\Auth\LoginModel;

class LoginController extends Controller {

	private LoginModel $loginModel;
	private Login $login;

	public function __construct() {
		$this->loginModel = new LoginModel();
	}

	public function auth(): Request {
		$aesDec = AES::decode(self::$request, 'AES_KEY', 'AES_IV');

		if (Security::validate((array) $aesDec, Login::getValidate('LoginController', 'auth'))) {
			$this->login = new Login($aesDec->users_email, $aesDec->users_password);
			$rsaDecode = RSA::decode((object) $this->loginModel->validateAccount($this->login));

			if (Security::passwordVerify($this->login->getUsersPassword(), $rsaDecode->users_password)) {
				$idusersDB = $this->loginModel->readUserDataDB($this->login);
				$idusers = AES::encode((object) $idusersDB, 'AES_KEY', 'AES_IV');

				return new Request('success', 'Autenticación exitosa.', [
					'jwt' => JWT::encode((array) $idusers, $_ENV['SERVER_TOKEN_TIME_EXP'])
				]);
			} else {
				return new Request('error', "El email/password no son correctos.");
			}
		} else {
			return new Request('error', "Todos los campos deben cumplir sus requerimientos.");
		}
	}

}