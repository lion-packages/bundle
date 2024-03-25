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
     * [List of all HTTP headers]
     *
     * @var array $headers;
     */
    private array $headers;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->headers = apache_request_headers();
    }

    /**
     * Protects defined web routes by validating a header with the hash defined
     * in the environment
     *
     * @return void
     */
    public function protectRouteList(): void
    {
        if (empty($this->headers['Lion-Auth'])) {
            finish(response(Response::SESSION_ERROR, 'Secure hash not found [1]', Request::HTTP_UNAUTHORIZED));
        }

        if ($_ENV['SERVER_HASH'] != $this->headers['Lion-Auth']) {
            finish(
                response(Response::SESSION_ERROR, 'You do not have access to this resource', Request::HTTP_UNAUTHORIZED)
            );
        }
    }
}
