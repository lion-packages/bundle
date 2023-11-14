<?php

declare(strict_types=1);

namespace LionBundle\Traits;

trait AES
{
    public function generateKeys(): string
    {
        $items = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@-_/*{}[].,#$&';
        $bytes = random_bytes(16);
        $key = '';

        for ($i = 0; $i < 16; $i++) {
            $key .= $items[(ord($bytes[$i]) % strlen($items))];
        }

        return $key;
    }
}
