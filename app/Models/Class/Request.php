<?php

namespace App\Models\Class;

use App\Traits\Singleton;

class Request {

    use Singleton;

    public function request(string $status, ?string $message = null, array|object $data = []): object {
        return (object) [
            'status' => $status,
            'message' => $message,
            'data' => $data
        ];
    }

    public function json() {

    }

}