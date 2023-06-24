<?php

namespace App\Http;

use App\Traits\Framework\HttpTrait;
use App\Traits\Framework\Singleton;

class Kernel {

    use Singleton, HttpTrait;

    public function new(string $name, mixed $value = null): Kernel {
        $_SESSION[$name] = $value;
        return $this;
    }

    public function get(mixed $key = null): mixed {
        return $key === null ? $_SESSION : $_SESSION[$key];
    }

    public function destroy(mixed $key = null): Kernel {
        if ($key === null) {
            session_unset();
            session_destroy();
        } else {
            unset($_SESSION[$key]);
        }

        return $this;
    }

}
