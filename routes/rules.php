<?php

/**
 * ------------------------------------------------------------------------------
 * Rules
 * ------------------------------------------------------------------------------
 * This is where you can register your rules for validating forms
 * ------------------------------------------------------------------------------
 **/

return [
    '/api/auth/login' => [
        \App\Rules\EmailRule::class,
        \App\Rules\PasswordRule::class
    ]
];