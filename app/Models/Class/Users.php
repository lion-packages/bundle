<?php

namespace App\Models\Class;

use App\Models\Class\DocumentTypes;

class Users implements \JsonSerializable {

	public function __construct(
		private ?int $idusers = null,
        private ?string $users_email = null,
        private ?string $users_password = null,
        private ?string $users_name = null,
        private ?string $users_last_name = null,
        private ?int $users_document = null,
        private ?DocumentTypes $documentTypes = null,
        private ?int $users_phone = null
    ) {}

    public function jsonSerialize() {
        return get_object_vars($this);
    }

    public static function validate(string $class, string $method): array {
        $list_validator = [
            'RegisterController' => [
                'createUser' => [
                    'lengthMin' => [
                        ['users_document', 6],
                        ['users_phone', 10],
                        ['users_name', 2],
                        ['users_last_name', 2]
                    ],
                    'lengthMax' => [
                        ['users_document', 10],
                        ['users_phone', 10]
                    ],
                    'required' => [
                        ['users_email', 'users_password', 'confirm_user_password', 'users_name', 'users_last_name', 'users_document', 'iddocument_types', 'users_phone']
                    ],
                    'email' => [
                        ['users_email']
                    ],
                    'equals' => [
                        ['users_password', 'confirm_user_password']
                    ],
                    'min' => [
                        ['iddocument_types', 1]
                    ],
                    'max' => [
                        ['iddocument_types', 3]
                    ]
                ]
            ],
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

    public function getIdusers() {
        return trim($this->idusers);
    }

    public function setIdusers($idusers) {
        $this->idusers = $idusers;
        return $this;
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

    public function getUsersName() {
        return trim(ucwords($this->users_name));
    }

    public function setUsersName($users_name) {
        $this->users_name = $users_name;
        return $this;
    }

    public function getUsersLastName() {
        return trim(ucwords($this->users_last_name));
    }

    public function setUsersLastName($users_last_name) {
        $this->users_last_name = $users_last_name;
        return $this;
    }

    public function getUsersDocument() {
        return trim((int) $this->users_document);
    }

    public function setUsersDocument($users_document) {
        $this->users_document = $users_document;
        return $this;
    }

    public function getDocumentTypes() {
        return $this->documentTypes;
    }

    public function setDocumentTypes($documentTypes) {
        $this->documentTypes = $documentTypes;
        return $this;
    }

    public function getUsersPhone() {
        return trim((int) $this->users_phone);
    }

    public function setUsersPhone($users_phone) {
        $this->users_phone = $users_phone;
        return $this;
    }

}