<?php

declare(strict_types=1);

namespace LionBundle\Commands\Route;

use LionBundle\Helpers\Http\Routes;
use LionCommand\Command;
use LionHelpers\Arr;
use LionHelpers\Str;
use LionRoute\Route;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;

class RouteListCommand extends Command
{
    private Arr $arr;
    private Str $str;

    private array $routes = [];
    private array $rules = [];
    private array $configMiddleware = [];

    /**
     * @required
     * */
    public function setArr(Arr $arr): RouteListCommand
    {
        $this->arr = $arr;

        return $this;
    }

    /**
     * @required
     * */
    public function setStr(Str $str): RouteListCommand
    {
        $this->str = $str;

        return $this;
    }

	protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->routes = json_decode(fetch(Route::GET, env->SERVER_URL . '/route-list')->getBody()->getContents(), true);
        $this->rules = Routes::getRules();
        $this->configMiddleware = Routes::getMiddleware();
    }

    protected function configure(): void
    {
        $this
            ->setName('route:list')
            ->setDescription("Command to view a list of available web routes");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        array_pop($this->routes);
        $this->configMiddleware = [...$this->configMiddleware['framework'], ...$this->configMiddleware['app']];
        $size = $this->arr->of($this->routes)->length();
        $cont = 0;
        $rows = [];

        foreach ($this->routes as $route => $methods) {
            foreach ($methods as $keyMethods => $method) {
                $routeUrl = $this->str->of("/{$route}")->replace("//", "/")->get();

                if ($method['handler']['request'] != false) {
                    $rows[] = [
                        $this->warningOutput($keyMethods),
                        $routeUrl,
                        $this->errorOutput('false'),
                        $this->errorOutput('false'),
                        "<href={$method['handler']['request']['url']}>[{$method['handler']['request']['url']}]</>"
                    ];
                }

                if ($method['handler']['callback'] != false) {
                    $rows[] = [
                        $this->warningOutput($keyMethods),
                        $routeUrl,
                        $this->errorOutput('false'),
                        $this->errorOutput('callback'),
                        $this->errorOutput('false'),
                    ];
                }

                if ($method['handler']['controller'] != false) {
                    $rows[] = [
                        $this->warningOutput($keyMethods),
                        $routeUrl,
                        $this->transformNamespace($method['handler']['controller']['name']),
                        $this->warningOutput($method['handler']['controller']['function']),
                        $this->errorOutput('false'),
                    ];
                }

                if ($this->arr->of($method['filters'])->length() > 0) {
                    foreach ($method['filters'] as $filter) {
                        foreach ($this->configMiddleware as $middlewareClass => $middlewareMethods) {
                            foreach ($middlewareMethods as $middlewareItem => $item) {
                                if ($filter === $item['name']) {
                                    $rows[] = [
                                        $this->infoOutput('MIDDLEWARE:'),
                                        $this->infoOutput($filter),
                                        $this->transformNamespace($middlewareClass),
                                        $this->warningOutput($item['method']),
                                        $this->errorOutput('false')
                                    ];
                                }
                            }
                        }
                    }
                }

                if (isset($this->rules[$keyMethods])) {
                    if (isset($this->rules[$keyMethods][$routeUrl])) {
                        foreach ($this->rules[$keyMethods][$routeUrl] as $keyUriRule => $classRule) {
                            $required_param = $classRule::$disabled === false ? 'REQUIRED' : 'OPTIONAL';

                            $rows[] = [
                                $this->successOutput('PARAM:'),
                                $this->successOutput($classRule::$field . " ({$required_param})"),
                                $this->transformNamespace($classRule),
                                $this->warningOutput('passes'),
                                $this->errorOutput('false')
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
                    ? $this->successOutput(' showing a single route ')
                    : $this->successOutput(' no routes available ')
                )
            )
            ->setHeaders(['METHOD', 'ROUTE', 'CLASS', 'FUNCTION', 'REQUEST'])
            ->setRows($rows)
            ->render();

        return Command::SUCCESS;
    }

    private function transformNamespace(string $namespace): string
    {
        $classNew = '';
        $split = explode("\\", $namespace);

        foreach ($split as $key => $value) {
            if ($key < (count($split) - 1)) {
                $classNew .= "{$this->purpleOutput($value)}\\";
            } else {
                $classNew .= $value;
            }
        }

        return $classNew;
    }
}
