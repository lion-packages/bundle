<?php

/**
 * ------------------------------------------------------------------------------
 * Start database service
 * ------------------------------------------------------------------------------
 * describe connections to establish connecting to multiple databases
 * ------------------------------------------------------------------------------
 **/

return [
    'default' => env->DB_NAME,
    'connections' => [
        env->DB_NAME => [
            'type' => env->DB_TYPE,
            'host' => env->DB_HOST,
            'port' => env->DB_PORT,
            'dbname' => env->DB_NAME,
            'user' => env->DB_USER,
            'password' => env->DB_PASSWORD
        ]
    ]
];