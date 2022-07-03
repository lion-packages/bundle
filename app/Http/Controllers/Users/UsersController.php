<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Users\UsersModel;

class UsersController extends Controller {

    private UsersModel $usersModel;

    public function __construct() {
        $this->init();
        $this->usersModel = new UsersModel();
    }

    public function createUsers(): object {
        if (!$this->usersModel->createUsersDB()) {
            return $this->response->error("An error occurred while creating the user");
        }

        return $this->response->success("user created successfully");
    }

    public function readUsers(?string $idusers = null): array|object {
        if ($idusers === null) {
            return $this->usersModel->readUsersDB();
        }

        $data = $this->usersModel->readUsersDB();
        $id = (int) $idusers;
        if ($id < 1 || $id > 5) {
            return $data[0];
        }

        return $data[$id - 1];
    }

}