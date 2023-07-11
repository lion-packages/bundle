<?php

/**
 * ------------------------------------------------------------------------------
 * Web middleware
 * ------------------------------------------------------------------------------
 * This is where you can register web middleware for your application
 * ------------------------------------------------------------------------------
 **/

return [
    'app' => [
        //
    ],
    'framework' => [
        \App\Http\Middleware\Framework\JWTMiddleware::class => [
            ['name' => "jwt-existence", 'method' => "existence"],
            ['name' => "jwt-authorize", 'method' => "authorize"],
            ['name' => "jwt-not-authorize", 'method' => "notAuthorize"],
            ['name' => "jwt-without-signature", 'method' => "authorizeWithoutSignature"]
        ]
    ],
];
