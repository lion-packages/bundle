<?php

declare(strict_types=1);

namespace Tests\Providers\Helpers;

trait HelpersProviderTrait
{
    public static function requestProvider(): array
    {
        return [
            [
                'key' => 'key',
                'value' => 10,
                'return' => 10,
            ],
            [
                'key' => 'key',
                'value' => 'root',
                'return' => 'root',
            ],
            [
                'key' => 'key',
                'value' => now()->format('Y-m-d'),
                'return' => now()->format('Y-m-d'),
            ],
            [
                'key' => 'key',
                'value' => [
                    'name' => 'root'
                ],
                'return' => [
                    'name' => 'root'
                ],
            ],
        ];
    }

    public static function envProvider(): array
    {
        return [
            [
                'envKey' => 'APP_NAME',
                'envValue' => 'lion-bundle',
                'return' => 'lion-bundle'
            ],
            [
                'envKey' => 'SERVER_URL',
                'envValue' => 'https://localhost',
                'return' => 'https://localhost'
            ],
            [
                'envKey' => 'APP_NAME_TEST',
                'envValue' => 'lion-bundle',
                'return' => 'lion-bundle'
            ],
            [
                'envKey' => 'SERVER_URL_TEST',
                'envValue' => 'https://localhost',
                'return' => 'https://localhost'
            ],
            [
                'envKey' => 'MAIL_DEBUG_SUPP_TEST',
                'envValue' => 'true',
                'return' => true
            ],
            [
                'envKey' => 'MAIL_DEBUG_SUPP_TEST',
                'envValue' => '(true)',
                'return' => true
            ],
            [
                'envKey' => 'MAIL_DEBUG_SUPP_TEST',
                'envValue' => 'false',
                'return' => false
            ],
            [
                'envKey' => 'MAIL_DEBUG_SUPP_TEST',
                'envValue' => '(false)',
                'return' => false
            ],
            [
                'envKey' => 'APP_DEBUG',
                'envValue' => 'empty',
                'return' => ''
            ],
            [
                'envKey' => 'APP_DEBUG',
                'envValue' => '(empty)',
                'return' => ''
            ],
            [
                'envKey' => 'APP_DEBUG_TEST',
                'envValue' => 'null',
                'return' => null
            ],
            [
                'envKey' => 'APP_DEBUG_TEST',
                'envValue' => '(null)',
                'return' => null
            ],
            [
                'envKey' => 'NOT_EXIST_ENVIROMENT',
                'envValue' => null,
                'return' => null,
            ],
            [
                'envKey' => 'REDIS_PORT',
                'envValue' => '6379',
                'return' => '6379',
            ],
        ];
    }
}
