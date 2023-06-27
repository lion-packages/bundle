<?php

/**
 * ------------------------------------------------------------------------------
 * Rules
 * ------------------------------------------------------------------------------
 * This is where you can register your rules for validating forms
 * ------------------------------------------------------------------------------
 **/

return [
    'POST' => [
        '/api/auth/login' => [
            \App\Rules\LionDatabase\Users\UsersEmailRule::class,
            \App\Rules\LionDatabase\Users\UsersPasswordRule::class
        ],
        '/api/user-registration' => [
            \App\Rules\LionDatabase\Users\IdrolesRule::class,
            \App\Rules\LionDatabase\Users\UsersNameRule::class,
            \App\Rules\LionDatabase\Users\UsersLastnameRule::class,
            \App\Rules\LionDatabase\Users\UsersEmailRule::class,
            \App\Rules\LionDatabase\Users\UsersPasswordRule::class
        ]
    ],
    'GET' => [
        //
    ],
    'PUT' => [
        //
    ],
    'DELETE' => [
        //
    ],
    'OPTIONS' => [
        //
    ],
    'PATCH' => [
        //
    ]
];
