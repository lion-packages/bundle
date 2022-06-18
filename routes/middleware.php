<?php

LionRoute\Route::newMiddleware([
    App\Http\Middleware\JWT\AuthorizationMiddleware::class => [
        ['name' => "jwt-exist", 'method' => "exist"],
        ['name' => "jwt-authorize", 'method' => "authorize"],
        ['name' => "jwt-not-authorize", 'method' => "notAuthorize"]
    ]
]);