<?php

declare(strict_types=1);

namespace LionBundle\Routes;

use DI\Container;
use Phroute\Phroute\HandlerResolverInterface;

class RouterResolver implements HandlerResolverInterface
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function resolve($handler)
    {
        if(is_array($handler) and is_string($handler[0])) {
            $handler[0] = $this->container->get($handler[0]);
        }

        return $handler;
    }
}
