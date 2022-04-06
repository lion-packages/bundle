<?php

namespace App\Http;

use App\Traits\Singleton;

class Response {

	use Singleton;

	public function response(string $status, ?string $message = null, array|object $data = []): object {
        return (object) [
            'status' => $status,
            'message' => $message,
            'data' => $data
        ];
    }

    public function success(?string $message = null, array|object $data = []): object {
    	return $this->response('success', $message, $data);
    }

    public function error(?string $message = null, array|object $data = []): object {
    	return $this->response('error', $message, $data);
    }

    public function warning(?string $message = null, array|object $data = []): object {
    	return $this->response('warning', $message, $data);
    }

    public function info(?string $message = null, array|object $data = []): object {
    	return $this->response('info', $message, $data);
    }

    public function toResponse(object $info): object {
        return $this->response($info->status, $info->message);
    }

}