<?php

/**
 * ------------------------------------------------------------------------------
 * Web middleware
 * ------------------------------------------------------------------------------
 * This is where you can register web middleware for your application
 * ------------------------------------------------------------------------------
 **/

LionRoute\Route::addMiddleware([
    App\Http\Middleware\JWT\AuthorizationMiddleware::class => [
        ['name' => "jwt-authorize", 'method' => "authorize"],
        ['name' => "jwt-not-authorize", 'method' => "notAuthorize"]
    ]
]);