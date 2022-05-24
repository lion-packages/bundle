<?php

namespace App\Http\Middleware\JWT;

use App\Http\Middleware\Middleware;
use LionSecurity\JWT;

class AuthorizationMiddleware extends Middleware {

	public function __construct() {
		$this->init();
	}

    public function notAuthorize(): void {
        $headers = apache_request_headers();

        if (isset($headers['Authorization'])) {
            $this->processOutput(
                $this->response->error('User in session, You must close the session')
            );
        }
    }

	public function authorize(): void {
		$headers = apache_request_headers();

        if (!isset($headers['Authorization'])) {
            $this->processOutput(
                $this->response->error('The JWT does not exist')
            );
        } else {
            if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
                $jwt = JWT::decode($matches[1]);

                if ($jwt->status === 'error') {
                    $this->processOutput($jwt);
                }
            } else {
                $this->processOutput(
                    $this->response->error('Invalid JWT')
                );
            }
        }
    }

}