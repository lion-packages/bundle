<?php

declare(strict_types=1);

namespace App\Http\Middleware\Framework;

use LionFiles\Store;
use LionSecurity\JWT;
use LionSecurity\RSA;

class JWTMiddleware
{
    private array $headers;

    public function __construct()
    {
        $this->headers = apache_request_headers();
    }

    private function validateSession($jwt): void
    {
        if (isError($jwt)) {
            finish(response->code(401)->response("session-error", $jwt->message));
        }

        if (!isset($jwt->data->jwt->data->session)) {
            finish(response->code(403)->response("session-error", 'undefined session'));
        }
    }

    public function existence(): void
    {
        if (!isset($this->headers['Authorization'])) {
            finish(response->code(401)->response("session-error", 'The JWT does not exist'));
        }
    }

    public function authorizeWithoutSignature(): void
    {
        $this->existence();
        $jwt = explode('.', JWT::get());

        if (arr->of($jwt)->length() != 3) {
            finish(response->code(401)->response("session-error", 'Invalid JWT [AWS-1]'));
        }

        $data = (object) ((object) json->decode(base64_decode($jwt[1])))->data;

        if (!isset($data->users_code)) {
            finish(response->code(403)->response("session-error", 'Invalid JWT [AWS]-2'));
        }

        $path = storage_path("keys/{$data->users_code}/");

        if (isError(Store::exist($path))) {
            finish(response->code(403)->response("session-error", 'Invalid JWT [AWS]-3'));
        }

        RSA::setPath(storage_path($path));
    }

    public function authorize(): void
    {
        $this->existence();
        $jwt = jwt();
        $this->validateSession($jwt);

        if (!$jwt->data->jwt->data->session) {
            finish(response->code(401)->response("session-error", 'User not logged in, you must log in'));
        }
    }

    public function notAuthorize(): void
    {
        $this->existence();
        $jwt = jwt();
        $this->validateSession($jwt);

        if ($jwt->data->jwt->data->session) {
            finish(response->code(401)->response("session-error", 'User in session, you must close the session'));
        }
    }
}
