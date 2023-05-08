<?php

namespace App\Http\Controllers\Auth;

use LionSecurity\JWT;

class SessionController {

    public function __construct() {

    }

    public function refresh() {
        return success("refreshed session token", [
            'jwt' => JWT::encode(
                (array) JWT::decode(JWT::get())->data
            )
        ]);
    }

}
