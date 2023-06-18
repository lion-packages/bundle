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
        ],
        env->MAIL_NAME_SUPP => [
            'services' => explode('-', env->MAIL_SERVICES_SUPP),
            'debug' => (int) env->MAIL_DEBUG_SUPP,
            'host' => env->MAIL_HOST_SUPP,
            'encryption' => env->MAIL_ENCRYPTION_SUPP,
            'port' => (int) env->MAIL_PORT_SUPP,
            'name' => env->MAIL_NAME_SUPP,
            'account' => env->MAIL_ACCOUNT_SUPP,
            'password' => env->MAIL_PASSWORD_SUPP
        ]
    ],
];
