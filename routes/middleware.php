<?php

use LionRoute\Route;
use App\Http\Middleware\JWT\AuthorizationMiddleware;

Route::newMiddleware([
    ['jwt-exist', AuthorizationMiddleware::class, 'exist'],
    ['jwt-authorize', AuthorizationMiddleware::class, 'authorize'],
    ['jwt-not-authorize', AuthorizationMiddleware::class, 'notAuthorize']
]);