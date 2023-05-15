<?php

namespace App\Http\Controllers\Auth;

use LionSecurity\JWT;

class SessionController {

    public function __construct() {

    }

    public function refresh() {
        $jwt = JWT::decode(JWT::get())->data;

        if (isset($jwt->refresh) && $jwt->refresh >= 3) {
            return error("you have exceeded the maximum amount of session refresh");
        }

        return success("refreshed session token", [
            'jwt' => JWT::encode([
                'refresh' => 1,
                ...((array) $jwt)
            ])
        ]);
    }

}
