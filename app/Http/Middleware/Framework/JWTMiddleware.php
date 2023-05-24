<?php

namespace App\Http\Middleware\Framework;

use App\Enums\Framework\StatusResponseEnum;
use LionFiles\Store;
use LionHelpers\Arr;
use LionSecurity\JWT;
use LionSecurity\RSA;

class JWTMiddleware {

    private array $headers;

    public function __construct() {
        $this->headers = apache_request_headers();
    }

    private function validateSession($jwt): void {
        if (isError($jwt)) {
            finish(response->response(StatusResponseEnum::SESSION_ERROR->value, $jwt->message));
        }

        if (!isset($jwt->data->session)) {
            finish(response->response(StatusResponseEnum::SESSION_ERROR->value, 'undefined session'));
        }
    }

    public function existence(): void {
        if (!isset($this->headers['Authorization'])) {
            finish(response->response(StatusResponseEnum::SESSION_ERROR->value, 'The JWT does not exist'));
        }
    }

    public function authorizeWithoutSignature() {
        $this->existence();
        $jwt = explode('.', JWT::get());

        if (Arr::of($jwt)->length() != 3) {
            finish(response->response(StatusResponseEnum::SESSION_ERROR->value, 'Invalid JWT'));
        }

        $data = (object) ((object) json->decode(base64_decode($jwt[1])))->data;

        // if (!isset($data->users_code)) {
        //     finish(response->response(StatusResponseEnum::SESSION_ERROR->value, 'Invalid JWT'));
        // }

        // $path = storage_path("keys/{$data->users_code}/");
        // $response = Store::exist($path);

        // if (isError($response)) {
        //     finish(response->response(StatusResponseEnum::SESSION_ERROR->value, 'Invalid JWT'));
        // }

        // RSA::$url_path = storage_path($path);
    }

    public function authorize(): void {
        $this->existence();

        if (preg_match('/Bearer\s(\S+)/', $this->headers['Authorization'], $matches)) {
            $jwt = JWT::decode($matches[1]);
            $this->validateSession($jwt);

            if (!$jwt->data->session) {
                finish(response->response(StatusResponseEnum::SESSION_ERROR->value, 'User not logged in, you must log in'));
            }
        } else {
            finish(response->response(StatusResponseEnum::SESSION_ERROR->value, 'Invalid JWT'));
        }
    }

    public function notAuthorize(): void {
        $this->existence();

        if (preg_match('/Bearer\s(\S+)/', $this->headers['Authorization'], $matches)) {
            $jwt = JWT::decode($matches[1]);
            $this->validateSession($jwt);

            if ($jwt->data->session) {
                finish(response->response(StatusResponseEnum::SESSION_ERROR->value, 'User in session, you must close the session'));
            }
        } else {
            finish(response->response(StatusResponseEnum::SESSION_ERROR->value, 'Invalid JWT'));
        }
    }

}
