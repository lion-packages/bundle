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
			'users_password' => $this->input->users_password,
			'confirm_user_password' => $this->input->confirm_user_password
		], 'AES_KEY', 'AES_IV');

		$this->input->iddocument_types = (int) $this->input->iddocument_types;
		$this->input->users_password = $aesDec->users_password;
		$this->input->confirm_user_password = $aesDec->confirm_user_password;

		if (!SECURITY::validate((array) $this->input, Users::validate('RegisterController', 'createUser'))) {
			return $this->request->request('error', "Todos los campos deben cumplir sus requerimientos.");
		}

		$this->users = new Users(null, $this->input->users_email, null, $this->input->users_name, $this->input->users_last_name, $this->input->users_document, new DocumentTypes($this->input->iddocument_types), $this->input->users_phone);

		$requestExistence = $this->validateColumns();
		if ($requestExistence->status === 'error') {
			return $requestExistence;
		}

		$rsaEnc = RSA::encode((object) [
			'users_password' => SECURITY::passwordHash($this->input->users_password),
		]);
		$this->users->setUsersPassword($rsaEnc->users_password);

		$request_create = $this->registerModel->createUserDB($this->users);
		return $this->request->request($request_create['status'], $request_create['message']);
	}

	private function validateColumns(): object {
		$existence = $this->registerModel->validateUserExistenceDB('users_email', $this->users->getUsersEmail());
		if ($existence['files'] > 0) {
			return $this->request->request('error', "Ya existe el Email: '{$this->users->getUsersEmail()}' dentro del sistema.");
		}

		$existence = $this->registerModel->validateUserExistenceDB('users_document', $this->users->getUsersDocument());
		if ($existence['files'] > 0) {
			return $this->request->request('error', "Ya existe el Documento: '{$this->users->getUsersDocument()}' dentro del sistema.");
		}

		$existence = $this->registerModel->validateUserExistenceDB('users_phone', $this->users->getUsersPhone());
		if ($existence['files'] > 0) {
			return $this->request->request('error', "Ya existe el Celular: '{$this->users->getUsersPhone()}' dentro del sistema.");
		}

		return $this->request->request('success');
	}

}