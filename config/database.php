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
        env->DB_NAME_PRISMA => [
            'type' => env->DB_TYPE_PRISMA,
            'host' => env->DB_HOST_PRISMA,
            'port' => env->DB_PORT_PRISMA,
            'dbname' => env->DB_NAME_PRISMA,
            'user' => env->DB_USER_PRISMA,
            'password' => env->DB_PASSWORD_PRISMA
        ],
        env->DB_NAME_TO_DO_LIST => [
            'type' => env->DB_TYPE_TO_DO_LIST,
            'host' => env->DB_HOST_TO_DO_LIST,
            'port' => env->DB_PORT_TO_DO_LIST,
            'dbname' => env->DB_NAME_TO_DO_LIST,
            'user' => env->DB_USER_TO_DO_LIST,
            'password' => env->DB_PASSWORD_TO_DO_LIST
        ],
        env->DB_NAME_CHATS => [
            'type' => env->DB_TYPE_CHATS,
            'host' => env->DB_HOST_CHATS,
            'port' => env->DB_PORT_CHATS,
            'dbname' => env->DB_NAME_CHATS,
            'user' => env->DB_USER_CHATS,
            'password' => env->DB_PASSWORD_CHATS
        ],
        env->DB_NAME_CONTROL_CENTER => [
            'type' => env->DB_TYPE_CONTROL_CENTER,
            'host' => env->DB_HOST_CONTROL_CENTER,
            'port' => env->DB_PORT_CONTROL_CENTER,
            'dbname' => env->DB_NAME_CONTROL_CENTER,
            'user' => env->DB_USER_CONTROL_CENTER,
            'password' => env->DB_PASSWORD_CONTROL_CENTER
        ],
        env->DB_NAME_SOLUMOVIL => [
            'type' => env->DB_TYPE_SOLUMOVIL,
            'host' => env->DB_HOST_SOLUMOVIL,
            'port' => env->DB_PORT_SOLUMOVIL,
            'dbname' => env->DB_NAME_SOLUMOVIL,
            'user' => env->DB_USER_SOLUMOVIL,
            'password' => env->DB_PASSWORD_SOLUMOVIL
        ]
    ]
];