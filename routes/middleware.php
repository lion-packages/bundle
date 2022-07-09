<?php

/**
 * ------------------------------------------------------------------------------
 * Web middleware
 * ------------------------------------------------------------------------------
 * This is where you can register web middleware for your application
 * ------------------------------------------------------------------------------
 **/

LionRoute\Route::newMiddleware([
    App\Http\Middleware\JWT\AuthorizationMiddleware::class => [
        ['name' => "jwt-exist", 'method' => "exist"],
        ['name' => "jwt-authorize", 'method' => "authorize"],
        ['name' => "jwt-not-authorize", 'method' => "notAuthorize"]
    ]
]);