<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Functions\{ Security, AES, RSA };
use App\Models\Auth\RegisterModel;
use App\Models\Class\{ Request, Users, DocumentTypes };

class RegisterController extends Controller {

	private RegisterModel $registerModel;

	public function __construct() {
		$this->registerModel = new RegisterModel();
	}

	public function createUser() {
		$aesDec = AES::decode(self::$request, 'AES_KEY', 'AES_IV');
		$aesDec->iddocument_types = (int) $aesDec->iddocument_types;

		if (Security::validate((array) $aesDec, Users::getValidate('RegisterController', 'createUser'))) {
			$rsaEnc = RSA::encode((object) [
				'users_password' => Security::passwordHash($aesDec->users_password),
			]);

			$users = new Users(null, $aesDec->users_email, $rsaEnc->users_password, $aesDec->users_name, $aesDec->users_last_name, $aesDec->users_document, new DocumentTypes($aesDec->iddocument_types), $aesDec->users_phone);

			$columns = ['users_email' => $users->getUsersEmail(), 'users_document' => $users->getUsersDocument(), 'users_phone' => $users->getUsersPhone()];
			foreach ($columns as $key => $column) {
				$existence = $this->registerModel->validateUserExistenceDB($key, $column);
				if ($existence['files'] != 0) {
					return new Request('error', "Ya existe '{$column}' dentro del sistema.");
					break;
				}
			}

			$request_create = $this->registerModel->createUserDB($users);
			return new Request($request_create['status'], $request_create['message']);
		} else {
			return new Request('error', "Todos los campos deben cumplir sus requerimientos.");
		}
	}

}