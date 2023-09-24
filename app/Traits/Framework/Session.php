<?php

declare(strict_types=1);

namespace App\Traits\Framework;

trait Session
{
    public function new(string $name, mixed $value = null): static
    {
        $_SESSION[$name] = $value;
        return $this;
    }

    public function get(mixed $key = null): mixed
    {
        return $key === null ? $_SESSION : $_SESSION[$key];
    }

    public function destroy(mixed $key = null): static
    {
        if ($key === null) {
            session_unset();
            session_destroy();
        } else {
            unset($_SESSION[$key]);
        }

        return $this;
    }
}
