<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use LionSecurity\{ SECURITY, AES, RSA };
use App\Models\Auth\RegisterModel;
use App\Models\Class\{ Users, DocumentTypes };

class RegisterController extends Controller {

	private RegisterModel $registerModel;
	private Users $users;

	public function __construct() {
		$this->init();
		$this->registerModel = new RegisterModel();
	}

	public function createUser(): object {
		$aesDec = AES::decode((object) [
			'users_password' => $this->request->users_password,
			'confirm_user_password' => $this->request->confirm_user_password
		], 'AES_KEY', 'AES_IV');

		$this->request->iddocument_types = (int) $this->request->iddocument_types;
		$this->request->users_password = $aesDec->users_password;
		$this->request->confirm_user_password = $aesDec->confirm_user_password;

		if (!SECURITY::validate((array) $this->request, Users::validate('RegisterController', 'createUser'))) {
			return $this->response->error("Todos los campos deben cumplir sus requerimientos.");
		}

		$this->users = new Users(null, $this->request->users_email, null, $this->request->users_name, $this->request->users_last_name, $this->request->users_document, new DocumentTypes($this->request->iddocument_types), $this->request->users_phone);

		$requestExistence = $this->validateColumns();
		if ($requestExistence->status === 'error') {
			return $requestExistence;
		}

		$rsaEnc = RSA::encode((object) [
			'users_password' => SECURITY::passwordHash($this->request->users_password),
		]);
		$this->users->setUsersPassword($rsaEnc->users_password);

		return $this->response->toResponse(
			$this->registerModel->createUserDB($this->users)
		);
	}

	private function validateColumns(): object {
		$existence = $this->registerModel->validateUserExistenceDB('users_email', $this->users->getUsersEmail());
		if ($existence->existence > 0) {
			return $this->response->error("Ya existe el Email: '{$this->users->getUsersEmail()}' dentro del sistema.");
		}

		$existence = $this->registerModel->validateUserExistenceDB('users_document', $this->users->getUsersDocument());
		if ($existence->existence > 0) {
			return $this->response->error("Ya existe el Documento: '{$this->users->getUsersDocument()}' dentro del sistema.");
		}

		$existence = $this->registerModel->validateUserExistenceDB('users_phone', $this->users->getUsersPhone());
		if ($existence->existence > 0) {
			return $this->response->error("Ya existe el Celular: '{$this->users->getUsersPhone()}' dentro del sistema.");
		}

		return $this->response->success();
	}

}