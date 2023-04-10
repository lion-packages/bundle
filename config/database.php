<?php

/**
 * ------------------------------------------------------------------------------
 * Start database service
 * ------------------------------------------------------------------------------
 * describe connections to establish connecting to multiple databases
 * ------------------------------------------------------------------------------
 **/

return [
    'default' => env->DB_DEFAULT_CONNECTION,
    'connections' => [
        env->DB_NAME => [
            'type' => env->DB_TYPE,
            'host' => env->DB_HOST,
            'port' => env->DB_PORT,
            'dbname' => env->DB_NAME,
            'user' => env->DB_USER,
            'password' => env->DB_PASSWORD
        ],
        env->DB_NAME_REQUEST => [
            'type' => env->DB_TYPE_REQUEST,
            'host' => env->DB_HOST_REQUEST,
            'port' => env->DB_PORT_REQUEST,
            'dbname' => env->DB_NAME_REQUEST,
            'user' => env->DB_USER_REQUEST,
            'password' => env->DB_PASSWORD_REQUEST
        ],
        env->DB_NAME_ENVIEXPRESS => [
            'type' => env->DB_TYPE_ENVIEXPRESS,
            'host' => env->DB_HOST_ENVIEXPRESS,
            'port' => env->DB_PORT_ENVIEXPRESS,
            'dbname' => env->DB_NAME_ENVIEXPRESS,
            'user' => env->DB_USER_ENVIEXPRESS,
            'password' => env->DB_PASSWORD_ENVIEXPRESS
        ]
    ]
];