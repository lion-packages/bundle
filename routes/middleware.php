<?php

declare(strict_types=1);

use Lion\Bundle\Middleware\RouteMiddleware;
use Lion\Bundle\Support\Http\Routes;

/**
 * -----------------------------------------------------------------------------
 * Web middleware
 * -----------------------------------------------------------------------------
 * This is where you can register web middleware for your application
 * -----------------------------------------------------------------------------
 **/

Routes::setMiddleware([
    'protect-route-list' => RouteMiddleware::class,
]);
