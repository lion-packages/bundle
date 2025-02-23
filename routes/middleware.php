<?php

declare(strict_types=1);

use Lion\Bundle\Helpers\Http\Routes;
use Lion\Bundle\Middleware\RouteMiddleware;

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
