<?php

/**
 * ------------------------------------------------------------------------------
 * Rules
 * ------------------------------------------------------------------------------
 * This is where you can register your rules for validating forms
 * ------------------------------------------------------------------------------
 **/

return [
    '/auth/login' => [
        \App\Rules\EmailRule::class,
        \App\Rules\PasswordRule::class
    ]
];