<?php

namespace App\Http\Middleware\JWT;

use App\Enums\Framework\StatusEnum;
use LionSecurity\JWT;

class AuthorizationMiddleware {

    private array $headers;

    public function __construct() {
        $this->headers = apache_request_headers();
    }

    private function exist(): void {
        if (!isset($this->headers['Authorization'])) {
            finish(response->response(StatusEnum::SESSION_ERROR->value, 'The JWT does not exist'));
        }
    }

    private function validateSession($jwt): void {
        if ($jwt->status === StatusEnum::ERROR->value) {
            finish(response->response(StatusEnum::SESSION_ERROR->value, $jwt->message));
        }

        if (!isset($jwt->data->session)) {
            finish(response->response(StatusEnum::SESSION_ERROR->value, 'undefined session'));
        }
    }

    public function authorize(): void {
        $this->exist();

        if (preg_match('/Bearer\s(\S+)/', $this->headers['Authorization'], $matches)) {
            $jwt = JWT::decode($matches[1]);
            $this->validateSession($jwt);

            if (!$jwt->data->session) {
                finish(response->response(StatusEnum::SESSION_ERROR->value, 'User not logged in, you must log in'));
            }
        } else {
            finish(response->response(StatusEnum::SESSION_ERROR->value, 'Invalid JWT'));
        }
    }

    public function notAuthorize(): void {
        $this->exist();

        if (preg_match('/Bearer\s(\S+)/', $this->headers['Authorization'], $matches)) {
            $jwt = JWT::decode($matches[1]);
            $this->validateSession($jwt);

            if ($jwt->data->session) {
                finish(response->response(StatusEnum::SESSION_ERROR->value, 'User in session, you must close the session'));
            }
        } else {
            finish(response->response(StatusEnum::SESSION_ERROR->value, 'Invalid JWT'));
        }
    }

}
