<?php

declare(strict_types=1);

namespace Lion\Bundle\Middleware;

use Lion\Bundle\Exceptions\MiddlewareException;
use Lion\Request\Http;
use Lion\Request\Status;
use Lion\Route\Interface\MiddlewareInterface;

/**
 * Responsible for filtering and validating the defined web routes
 *
 * @package Lion\Bundle\Middleware
 */
class RouteMiddleware implements MiddlewareInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws MiddlewareException
     */
    public function process(): void
    {
        if (empty($_SERVER['HTTP_LION_AUTH'])) {
            throw new MiddlewareException('Secure hash not found', Status::ERROR, Http::UNAUTHORIZED);
        }

        if ($_ENV['SERVER_HASH'] != $_SERVER['HTTP_LION_AUTH']) {
            throw new MiddlewareException('You do not have access to this resource', Status::ERROR, Http::UNAUTHORIZED);
        }
    }
}
