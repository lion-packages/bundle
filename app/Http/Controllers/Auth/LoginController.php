<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use LionSecurity\{ SECURITY, AES, JWT, RSA };
use App\Models\Class\Users;
use App\Models\Auth\LoginModel;

class LoginController extends Controller {

	private LoginModel $loginModel;
	private Users $users;

	public function __construct() {
		$this->init();
		$this->loginModel = new LoginModel();
	}

	public function auth(): object {
		$aesDec = AES::decode((object) [
			'users_password' => $this->input->users_password
		], 'AES_KEY', 'AES_IV');
		$this->input->users_password = $aesDec->users_password;

		if (!SECURITY::validate((array) $this->input, Users::validate('LoginController', 'auth'))) {
			return $this->request->request('error', "Todos los campos deben cumplir sus requerimientos.");
		}

		$this->users = new Users(null, $this->input->users_email, null);

		$passwordDB = $this->loginModel->validateAccount($this->users);
		if (isset($passwordDB['status'])) {
			return $this->request->request('error', "El email/password no son correctos. [1]");
		}

		$rsaDec = RSA::decode((object) [
			'users_password' => $passwordDB['users_password']
		]);

		if (!SECURITY::passwordVerify($this->input->users_password, $rsaDec->users_password)) {
			return $this->request->request('error', "El email/password no son correctos. [2]");
		}

		$idusersDB = $this->loginModel->readUserDataDB($this->users);
		return $this->request->request('success', 'Autenticación exitosa.', [
			'jwt' => JWT::encode($idusersDB)
		]);
	}

}