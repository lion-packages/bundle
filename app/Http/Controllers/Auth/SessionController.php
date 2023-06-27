<?php

namespace App\Http\Controllers\Auth;

use LionSecurity\JWT;

class SessionController {

    public function __construct() {

    }

    public function refresh() {
        $jwt = jwt();

        if (isset($jwt->refresh) && $jwt->refresh >= 3) {
            return error(500, "you have exceeded the maximum amount of session refresh");
        }

        return success(200, "refreshed session token", [
            'jwt' => JWT::encode([
                'refresh' => isset($jwt->refresh) ? (((int) $jwt->refresh) + 1) : 1,
                ...((array) $jwt)
            ])
        ]);
    }

}
