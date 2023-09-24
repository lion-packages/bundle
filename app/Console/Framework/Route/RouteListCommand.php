<?php

namespace App\Console\Framework\Route;

use App\Traits\Framework\ConsoleOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;

class RouteListCommand extends Command
{
    use ConsoleOutput;

	protected static $defaultName = "route:list";

	protected function initialize(InputInterface $input, OutputInterface $output)
    {

    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {

    }

    protected function configure()
    {
        $this
            ->setDescription("Command to view a list of available web routes");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $routes = fetch("GET", env->SERVER_URL . "/route-list");
        array_pop($routes);
        $rules = require_once("./routes/rules.php");
        $config_middleware = require_once("./config/middleware.php");
        $config_middleware = [...$config_middleware['framework'], ...$config_middleware['app']];
        $size = arr->of($routes)->length();
        $cont = 0;
        $rows = [];

        $transformNamespace = function(string $namespace): string {
            $class_new = "";
            $split = explode("\\", $namespace);

            foreach ($split as $key => $value) {
                if ($key < (count($split) - 1)) {
                    $class_new .= $this->purpleOutput($value) . "\\";
                } else {
                    $class_new .= $value;
                }
            }

            return $class_new;
        };

        foreach ($routes as $route => $methods) {
            foreach ($methods as $keyMethods => $method) {
                $route_url = str->of("/{$route}")->replace("//", "/")->get();

                if ($method['handler']['request'] != false) {
                    $rows[] = [
                        $this->warningOutput($keyMethods),
                        $route_url,
                        $this->errorOutput("false"),
                        $this->errorOutput("false"),
                        "<href={$method['handler']['request']['url']}>[{$method['handler']['request']['url']}]</>"
                    ];
                }

                if ($method['handler']['callback'] != false) {
                    $rows[] = [
                        $this->warningOutput($keyMethods),
                        $route_url,
                        $this->errorOutput("false"),
                        $this->errorOutput("callback"),
                        $this->errorOutput("false"),
                    ];
                }

                if ($method['handler']['controller'] != false) {
                    $rows[] = [
                        $this->warningOutput($keyMethods),
                        $route_url,
                        $transformNamespace($method['handler']['controller']['name']),
                        $this->warningOutput($method['handler']['controller']['function']),
                        $this->errorOutput("false"),
                    ];
                }

                if (arr->of($method['filters'])->length() > 0) {
                    foreach ($method['filters'] as $key => $filter) {
                        foreach ($config_middleware as $middlewareClass => $middlewareMethods) {
                            foreach ($middlewareMethods as $middlewareItem => $item) {
                                if ($filter === $item['name']) {
                                    $rows[] = [
                                        $this->infoOutput("MIDDLEWARE:"),
                                        $this->infoOutput($filter),
                                        $transformNamespace($middlewareClass),
                                        $this->warningOutput($item['method']),
                                        $this->errorOutput("false")
                                    ];
                                }
                            }
                        }
                    }
                }

                if (isset($rules[$keyMethods])) {
                    if (isset($rules[$keyMethods][$route_url])) {
                        foreach ($rules[$keyMethods][$route_url] as $key_uri_rule => $class_rule) {
                            $required_param = $class_rule::$disabled === false ? "REQUIRED" : "OPTIONAL";
                            $rows[] = [
                                $this->successOutput("PARAM:"),
                                $this->successOutput($class_rule::$field . " ({$required_param})"),
                                $transformNamespace($class_rule),
                                $this->warningOutput("passes"),
                                $this->errorOutput("false")
                            ];
                        }
                    }
                }

                if ($cont < ($size - 1)) {
                    $rows[] = new TableSeparator();
                }

                $cont++;
            }
        }

        (new Table($output))
            ->setHeaderTitle($this->successOutput('ROUTES'))
            ->setFooterTitle(
                $size > 1
                ? $this->successOutput(" showing [{$size}] routes ")
                : ($size === 1
                    ? $this->successOutput(" showing a single route ")
                    : $this->successOutput(" no routes available ")
                )
            )
            ->setHeaders(['METHOD', 'ROUTE', 'CLASS', 'FUNCTION', 'REQUEST'])
            ->setRows($rows)
            ->render();

        return Command::SUCCESS;
    }
}
