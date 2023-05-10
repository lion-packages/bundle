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
        ],
        env->DB_NAME_CONTABILIDAD => [
            'type' => env->DB_TYPE_CONTABILIDAD,
            'host' => env->DB_HOST_CONTABILIDAD,
            'port' => env->DB_PORT_CONTABILIDAD,
            'dbname' => env->DB_NAME_CONTABILIDAD,
            'user' => env->DB_USER_CONTABILIDAD,
            'password' => env->DB_PASSWORD_CONTABILIDAD
        ],
        env->DB_NAME_DIGITALIZACION => [
            'type' => env->DB_TYPE_DIGITALIZACION,
            'host' => env->DB_HOST_DIGITALIZACION,
            'port' => env->DB_PORT_DIGITALIZACION,
            'dbname' => env->DB_NAME_DIGITALIZACION,
            'user' => env->DB_USER_DIGITALIZACION,
            'password' => env->DB_PASSWORD_DIGITALIZACION
        ]
    ]
];
