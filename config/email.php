<?php

/**
 * ------------------------------------------------------------------------------
 * Start mail service
 * ------------------------------------------------------------------------------
 * describe connections to establish connecting to multiple databases
 * ------------------------------------------------------------------------------
 **/

return [
    'default' => env->MAIL_NAME,
    'accounts' => [
        env->MAIL_NAME => [
            'services' => explode('-', env->MAIL_SERVICES),
            'debug' => (int) env->MAIL_DEBUG,
            'host' => env->MAIL_HOST,
            'encryption' => env->MAIL_ENCRYPTION,
            'port' => (int) env->MAIL_PORT,
            'name' => env->MAIL_NAME,
            'account' => env->MAIL_ACCOUNT,
            'password' => env->MAIL_PASSWORD
        ]
    ],
];
