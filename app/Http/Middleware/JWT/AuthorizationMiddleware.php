<?php

namespace App\Http\Middleware\JWT;

use LionSecurity\JWT;

class AuthorizationMiddleware {

    public function __construct() {

    }

    public function exist(): void {
        $headers = apache_request_headers();

        if (!isset($headers['Authorization'])) {
            response->finish(json->encode(response->error('The JWT does not exist')));
        }
    }

    public function authorize(): void {
        $headers = apache_request_headers();

        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            $jwt = JWT::decode($matches[1]);

            if ($jwt->status === 'error') {
                response->finish(json->encode(response->error($jwt->message)));
            }
        } else {
            response->finish(json->encode(response->error('Invalid JWT')));
        }
    }

    public function notAuthorize(): void {
        $headers = apache_request_headers();

        if (isset($headers['Authorization'])) {
            response->finish(json->encode(response->error('User in session, You must close the session')));
        }
    }

}