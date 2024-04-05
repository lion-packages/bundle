<?php

declare(strict_types=1);

namespace Tests\Providers\Helpers;

trait EnvProviderTrait
{
    public static function getKeyProvider(): array
    {
        return [
            [
                'value' => 'http://127.0.0.1:8000',
                'return' => 'SERVER_URL',
            ],
            [
                'value' => 'http://127.0.0.1:5173',
                'return' => 'SERVER_URL_AUD'
            ],
            [
                'value' => uniqid(),
                'return' => false
            ]
        ];
    }
}
