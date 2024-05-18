<?php

declare(strict_types=1);

namespace Tests\Providers\Helpers;

trait EnvProviderTrait
{
    public static function getProvider(): array
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
            ],
        ];
    }

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

    public static function getOptionProvider(): array
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
            ],
            [
                'envKey' => 'key',
                'envValue' => 'default',
                'return' => 'value',
            ],
            [
                'envKey' => 'key2',
                'envValue' => 'default2',
                'return' => 'value',
            ],
        ];
    }
}
