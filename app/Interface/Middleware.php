<?php

namespace App\Interface;

use Phroute\Phroute\RouteCollector;

interface Middleware {

	public static function createMiddleware(array $filter): void;

}