<?php

namespace LionRoute;

use LionRoute\Middleware;
use LionRoute\Request;

class Route {

	private static $router;
	private static array $class_map;
	
	public function __construct() {
		
	}

	public static function init(array $filters): void {
		self::$class_map = $filters['class'];
		$parser = isset(self::$class_map['RouteParser']) ? new self::$class_map['RouteParser']() : null;
		self::$router = new self::$class_map['RouteCollector']($parser);
		self::createMiddleware(isset($filters['middleware']) ? $filters['middleware'] : []);
		$_POST = json_decode(file_get_contents("php://input"), true);
	}

	public static function createMiddleware(array $filters): void {
		if (count($filters) > 0) {
			foreach ($filters as $key => $obj) {
				self::$router->filter($obj->getMiddlewareName(), function() use ($obj) {    
					$objectClass = $obj->getNewObjectClass();
					$methodClass = $obj->getMethodClass();

					$objectClass->$methodClass();
				});
			}
		}
	}

	public static function prefix(string $prefix_name, \Closure $closure): void {
		self::$router->group(['prefix' => $prefix_name], function($router) use ($closure) {
			$closure();
		});
	}

	public static function middleware(array $middleware, \Closure $closure): void {
		self::$router->group($middleware, function($router) use ($closure) {
			$closure();
		});
	}

	public static function any(string $url, \Closure|array $controller_function, array $filters = []): void {
		if (count($filters) > 0) {
			self::$router->any($url, $controller_function, $filters);
		} else {
			self::$router->any($url, $controller_function);
		}
	}

	public static function delete(string $url, \Closure|array $controller_function, array $filters = []): void {
		if (count($filters) > 0) {
			self::$router->delete($url, $controller_function, $filters);
		} else {
			self::$router->delete($url, $controller_function);
		}
	}

	public static function post(string $url, \Closure|array $controller_function, array $filters = []): void {
		if (count($filters) > 0) {
			self::$router->post($url, $controller_function, $filters);
		} else {
			self::$router->post($url, $controller_function);
		}
	}

	public static function put(string $url, \Closure|array $controller_function, array $filters = []): void {
		if (count($filters) > 0) {
			self::$router->put($url, $controller_function, $filters);
		} else {
			self::$router->put($url, $controller_function);
		}
	}

	public static function get(string $url, \Closure|array $controller_function, array $filters = []): void {
		if (count($filters) > 0) {
			self::$router->get($url, $controller_function, $filters);
		} else {
			self::$router->get($url, $controller_function);
		}
	}

	public static function newMiddleware(string $middlewareName, string $objectClass, string $methodClass): Middleware {
		return new Middleware($middlewareName, $objectClass, $methodClass);
	}

	private static function processInput($index): string {
		return implode('/', array_slice(explode('/', $_SERVER['REQUEST_URI']), $index));
	}

	public static function processOutput($response): void {
		echo(json_encode($response));
	}

	public static function dispatch($index) {
		try {
			return (new self::$class_map['Dispatcher'](self::$router->getData()))->dispatch(
				$_SERVER['REQUEST_METHOD'], 
				self::processInput($index)
			);
		} catch (\Exception $e) {
			return new Request("error", "Page not found. [Dispatch]");
		}
	}

}