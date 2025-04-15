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
                'envKey' => 'NOT_EXIST_ENVIROMENT',
                'envValue' => false,
                'return' => false,
            ],
            [
                'envKey' => 'NOT_EXIST_ENVIROMENT',
                'envValue' => true,
                'return' => true,
            ],
        ];
    }

    public static function getKeyProvider(): array
    {
        return [
            [
                'value' => 'https://localhost',
                'return' => 'SERVER_URL',
            ],
            [
                'value' => 'http://localhost:5173',
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
