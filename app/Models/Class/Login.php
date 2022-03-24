<?php

namespace App\Models\Class;

class Login implements \JsonSerializable {

	public function __construct(
		private ?string $users_email = null,
		private ?string $users_password = null
	) {}

	public function jsonSerialize() {
        return get_object_vars($this);
    }

    public static function getValidate(string $class, string $method): array {
        $list_validator = [
            'LoginController' => [
                'auth' => [
                    'email' => [
                        ['users_email']
                    ],
                    'required' => [
                        ['users_email', 'users_password']
                    ],
                    'lengthMin' => [
                        ['users_password', 64]
                    ],
                    'lengthMax' => [
                        ['users_password', 64]
                    ]
                ]
            ]
        ];

        return $list_validator[$class][$method];
    }

    public function getUsersEmail() {
        return trim(strtolower($this->users_email));
    }

    public function setUsersEmail($users_email) {
        $this->users_email = $users_email;
        return $this;
    }

    public function getUsersPassword() {
        return trim($this->users_password);
    }

    public function setUsersPassword($users_password) {
        $this->users_password = $users_password;
        return $this;
    }

}