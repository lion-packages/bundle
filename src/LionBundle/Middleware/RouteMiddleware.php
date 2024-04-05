<?php

declare(strict_types=1);

namespace Lion\Bundle\Middleware;

use Lion\Request\Request;
use Lion\Request\Response;

/**
 * Responsible for filtering and validating the defined web routes
 *
 * @package Lion\Bundle\Middleware
 */
class RouteMiddleware
{
    /**
     * Protects defined web routes by validating a header with the hash defined
     * in the environment
     *
     * @return void
     */
    public function protectRouteList(): void
    {
        if (empty($_SERVER['HTTP_LION_AUTH'])) {
            finish(response(Response::SESSION_ERROR, 'Secure hash not found [1]', Request::HTTP_UNAUTHORIZED));
        }

        if ($_ENV['SERVER_HASH'] != $_SERVER['HTTP_LION_AUTH']) {
            finish(
                response(Response::SESSION_ERROR, 'You do not have access to this resource', Request::HTTP_UNAUTHORIZED)
            );
        }
    }
}
