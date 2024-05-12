<?php

declare(strict_types=1);

namespace Tests\Providers\Helpers;

trait HelpersProviderTrait
{
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
                'envValue' => 'http://127.0.0.1:8000',
                'return' => 'http://127.0.0.1:8000'
            ],
            [
                'envKey' => 'APP_NAME_TEST',
                'envValue' => 'lion-bundle',
                'return' => 'lion-bundle'
            ],
            [
                'envKey' => 'SERVER_URL_TEST',
                'envValue' => 'http://127.0.0.1:8000',
                'return' => 'http://127.0.0.1:8000'
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
            ]
        ];
    }
}
