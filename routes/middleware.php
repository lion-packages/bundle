<?php

use App\Http\Middleware\JWT\AuthorizationMiddleware;

LionRoute\Route::newMiddleware([
    ['jwt-exist', AuthorizationMiddleware::class, 'exist'],
    ['jwt-authorize', AuthorizationMiddleware::class, 'authorize'],
    ['jwt-not-authorize', AuthorizationMiddleware::class, 'notAuthorize']
]);