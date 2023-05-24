<?php

namespace App\Http\Middleware\Framework;

use App\Enums\Framework\StatusEnum;
use LionSecurity\JWT;

class JWTMiddleware {

    private array $headers;

    public function __construct() {
        $this->headers = apache_request_headers();
    }

    private function validateSession($jwt): void {
        if (StatusEnum::isError($jwt)) {
            finish(response->response(StatusEnum::SESSION_ERROR->value, $jwt->message));
        }

        if (!isset($jwt->data->session)) {
            finish(response->response(StatusEnum::SESSION_ERROR->value, 'undefined session'));
        }
    }

    public function existence(): void {
        if (!isset($this->headers['Authorization'])) {
            finish(response->response(StatusEnum::SESSION_ERROR->value, 'The JWT does not exist'));
        }
    }

    public function authorize(): void {
        $this->existence();

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
        $this->existence();

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
