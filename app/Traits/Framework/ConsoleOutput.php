<?php

namespace App\Traits\Framework;

trait ConsoleOutput {

    public function errorOutput(string $message = ""): string {
        return "\033[0;31m{$message}\033[0m";
    }

    public function successOutput(string $message = ""): string {
        return "\033[0;32m{$message}\033[0m";
    }

    public function warningOutput(string $message = ""): string {
        return "\033[0;33m{$message}\033[0m";
    }

}
