<?php

declare(strict_types=1);

namespace Lion\Bundle\Middleware;

use Lion\Bundle\Exceptions\MiddlewareException;
use Lion\Request\Http;
use Lion\Request\Status;
use Lion\Route\Interface\MiddlewareInterface;

/**
 * Verify that HTTP protocols are accepted
 *
 * @package Lion\Bundle\Middleware
 */
class HttpsMiddleware implements MiddlewareInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws MiddlewareException [If the HTTP protocol is not secure]
     */
    public function process(): void
    {
        if (!isset($_SERVER['HTTPS'])) {
            throw new MiddlewareException(
                'the HTTPS protocol header is not set, the connection must be secure (HTTPS)',
                Status::ERROR,
                Http::FORBIDDEN
            );
        }

        if ($_SERVER['HTTPS'] !== 'on') {
            throw new MiddlewareException(
                'the connection is not marked as secure (HTTPS is not active)',
                Status::ERROR,
                Http::FORBIDDEN
            );
        }
    }
}
