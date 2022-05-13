<?php

namespace App\Http\Request;

use App\Traits\Singleton;

class Response {

	use Singleton;

	public static function response(string $status, ?string $message = null, array|object $data = []): object {
        return (object) [
            'status' => $status,
            'message' => $message,
            'data' => $data
        ];
    }

    public static function success(?string $message = null, array|object $data = []): object {
    	return self::response('success', $message, $data);
    }

    public static function error(?string $message = null, array|object $data = []): object {
    	return self::response('error', $message, $data);
    }

    public static function warning(?string $message = null, array|object $data = []): object {
    	return self::response('warning', $message, $data);
    }

    public static function info(?string $message = null, array|object $data = []): object {
    	return self::response('info', $message, $data);
    }

}