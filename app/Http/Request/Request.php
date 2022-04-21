<?php

namespace App\Http\Request;

use App\Traits\Singleton;

class Request {

    use Singleton;

    public static function request(): object {
        $content = json_decode(file_get_contents("php://input"), true);
        return $content === null ? (object) ($_POST + $_FILES + $_GET) : (object) $content;
    }

    public static function env(): object {
        return (object) $_ENV;
    }

}