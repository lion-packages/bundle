<?php

declare(strict_types=1);

namespace Lion\Bundle\Middleware;

use Lion\Bundle\Exceptions\MiddlewareException;
use Lion\Request\Http;
use Lion\Request\Status;

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
     *
     * @throws MiddlewareException [if the hash does not meet the requirements]
     */
    public function protectRouteList(): void
    {
        if (empty($_SERVER['HTTP_LION_AUTH'])) {
            throw new MiddlewareException('secure hash not found', Status::SESSION_ERROR, Http::UNAUTHORIZED);
        }

        if ($_ENV['SERVER_HASH'] != $_SERVER['HTTP_LION_AUTH']) {
            throw new MiddlewareException(
                'you do not have access to this resource',
                Status::SESSION_ERROR,
                Http::UNAUTHORIZED
            );
        }
    }
}
