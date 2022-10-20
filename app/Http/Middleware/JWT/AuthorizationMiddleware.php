<?php

namespace App\Http\Middleware\JWT;

use LionSecurity\JWT;

class AuthorizationMiddleware {

    private array $headers;

    public function __construct() {
        $this->headers = apache_request_headers();
    }

    private function exist(): void {
        if (!isset($this->headers['Authorization'])) {
            response->finish(response->response('session-error', 'The JWT does not exist'));
        }
    }

    private function validateSession($jwt): void {
        if ($jwt->status === 'error') {
            response->finish(response->response('session-error', $jwt->message));
        }

        if (!isset($jwt->data->session)) {
            response->finish(response->response('session-error', 'undefined session'));
        }
    }

    public function authorize(): void {
        $this->exist();

        if (preg_match('/Bearer\s(\S+)/', $this->headers['Authorization'], $matches)) {
            $jwt = JWT::decode($matches[1]);
            $this->validateSession($jwt);

            if (!$jwt->data->session) {
                response->finish(response->response('session-error', 'User not logged in, you must log in'));
            }
        } else {
            response->finish(response->response('session-error', 'Invalid JWT'));
        }
    }

    public function notAuthorize(): void {
        $this->exist();

        if (preg_match('/Bearer\s(\S+)/', $this->headers['Authorization'], $matches)) {
            $jwt = JWT::decode($matches[1]);
            $this->validateSession($jwt);

            if ($jwt->data->session) {
                response->finish(response->response('session-error', 'User in session, you must close the session'));
            }
        } else {
            response->finish(response->response('session-error', 'Invalid JWT'));
        }
    }

}