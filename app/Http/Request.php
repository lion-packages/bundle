<?php

namespace App\Http;

use App\Traits\Singleton;

class Request {

    use Singleton;

    public static function request(): object {
        $content = json_decode(file_get_contents("php://input"), true);
        return $content === null ? (object) ($_POST + $_FILES + $_GET) : (object) $content;
    }

}