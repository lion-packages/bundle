<?php

namespace App\Http\Middleware\JWT;

use App\Http\Middleware\Middleware;
use LionSecurity\JWT;
use LionRequest\{ Json, Response };

class AuthorizationMiddleware extends Middleware {

    public function __construct() {
        $this->init();
    }

    public function exist(): void {
        $headers = apache_request_headers();

        if (!isset($headers['Authorization'])) {
            Response::finish(
                Json::encode($this->response->error('The JWT does not exist'))
            );
        }
    }

    public function authorize(): void {
        $headers = apache_request_headers();

        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            $jwt = JWT::decode($matches[1]);
            if ($jwt->status === 'error') Response::finish(Json::encode($jwt));
        } else {
            Response::finish(
                Json::encode($this->response->error('Invalid JWT'))
            );
        }
    }

    public function notAuthorize(): void {
        $headers = apache_request_headers();

        if (isset($headers['Authorization'])) {
            Response::finish(
                Json::encode($this->response->error('User in session, You must close the session'))
            );
        }
    }

}