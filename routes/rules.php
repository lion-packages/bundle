<?php

/**
 * ------------------------------------------------------------------------------
 * Rules
 * ------------------------------------------------------------------------------
 * This is where you can register your rules for validating forms
 * ------------------------------------------------------------------------------
 **/

return [
    'ANY' => [
        //
    ],
    'POST' => [
        '/api/auth/login' => [
            \App\Rules\LionDatabase\Users\UsersEmailRule::class,
            \App\Rules\LionDatabase\Users\UsersPasswordRule::class
        ],
        '/api/user-registration' => [
            App\Rules\LionDatabase\Roles\IdrolesRule::class,
            \App\Rules\LionDatabase\Users\UsersNameRule::class,
            \App\Rules\LionDatabase\Users\UsersLastNameRule::class,
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
];
