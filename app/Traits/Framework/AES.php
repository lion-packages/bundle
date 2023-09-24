<?php

declare(strict_types=1);

namespace App\Traits\Framework;

trait AES
{
    public function generateKeys(): string
    {
        $items = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@-_/*{}[].,#$&';
        $bytes = random_bytes(16);
        $longitud = strlen($items);
        $key = '';

        for ($i = 0; $i < 16; $i++) {
            $indice = ord($bytes[$i]) % $longitud;
            $key .= $items[$indice];
        }

        return $key;
    }
}
