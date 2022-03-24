<?php

namespace App\Models\Class;

class Request implements \JsonSerializable {

	public function __construct(
		private ?string $status = null,
		private ?string $message = null,
        private array|object $data = []
	) {}

	public function jsonSerialize() {
        return get_object_vars($this);
    }

    public function getStatus() {
        return strtolower($this->status);
    }

    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    public function getMessage() {
        return strtolower($this->message);
    }

    public function setMessage($message) {
        $this->message = $message;
        return $this;
    }

    public function getData() {
        return $this->data;
    }

    public function setData($data) {
        $this->data = $data;
        return $this;
    }

}