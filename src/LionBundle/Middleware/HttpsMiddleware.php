<?php

declare(strict_types=1);

namespace Lion\Bundle\Middleware;

use Lion\Bundle\Exceptions\MiddlewareException;
use Lion\Request\Http;
use Lion\Request\Status;
use Lion\Route\Interface\MiddlewareInterface;

/**
 * Verify that HTTP protocols are accepted.
 */
class HttpsMiddleware implements MiddlewareInterface
{
    /**
     * Filter name.
     *
     * @const NAME
     */
    public const string NAME = 'https';

    /**
     * {@inheritDoc}
     *
     * @throws MiddlewareException If something goes wrong with the filter.
     */
    public function process(): void
    {
        if (!isset($_SERVER['HTTPS'])) {
            throw new MiddlewareException(
                'The HTTPS protocol header is not set, the connection must be secure (HTTPS).',
                Status::ERROR,
                Http::FORBIDDEN
            );
        }

        if ($_SERVER['HTTPS'] !== 'on') {
            throw new MiddlewareException(
                'The connection is not marked as secure (HTTPS is not active).',
                Status::ERROR,
                Http::FORBIDDEN
            );
        }
    }
}
