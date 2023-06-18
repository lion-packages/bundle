<?php

/**
 * ------------------------------------------------------------------------------
 * Start database service
 * ------------------------------------------------------------------------------
 * describe connections to establish connecting to multiple databases
 * ------------------------------------------------------------------------------
 **/

return [
    'default' => env->DB_NAME_ICI,
    'connections' => [
        // env->DB_NAME => [
        //     'type' => env->DB_TYPE,
        //     'host' => env->DB_HOST,
        //     'port' => env->DB_PORT,
        //     'dbname' => env->DB_NAME,
        //     'user' => env->DB_USER,
        //     'password' => env->DB_PASSWORD
        // ],
        env->DB_NAME_ICI => [
            'type' => env->DB_TYPE_ICI,
            'host' => env->DB_HOST_ICI,
            'port' => env->DB_PORT_ICI,
            'dbname' => env->DB_NAME_ICI,
            'user' => env->DB_USER_ICI,
            'password' => env->DB_PASSWORD_ICI
        ],
    ]
];
