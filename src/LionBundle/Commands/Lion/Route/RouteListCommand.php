<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Route;

use Lion\Bundle\Helpers\Http\Routes;
use Lion\Command\Command;
use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use Lion\Route\Route;
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
            ->setDescription('Command to view a list of available web routes');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        array_pop($this->routes);
        $size = $this->arr->of($this->routes)->length();
        $cont = 0;
        $rows = [];

        foreach ($this->routes as $route => $methods) {
            foreach ($methods as $keyMethods => $method) {
                $routeUrl = $this->str->of("/{$route}")->replace("//", "/")->get();
                if ($method['handler']['callback'] != false) {
                    $rows[] = [
                        $this->warningOutput($keyMethods),
                        $routeUrl,
                        $this->errorOutput('false'),
                        $this->errorOutput('callback')
                    ];
                }

                if ($method['handler']['controller'] != false) {
                    $rows[] = [
                        $this->warningOutput($keyMethods),
                        $routeUrl,
                        $this->transformNamespace($method['handler']['controller']['name']),
                        $this->warningOutput($method['handler']['controller']['function'])
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
                                        $this->warningOutput($item['method'])
                                    ];
                                }
                            }
                        }
                    }
                }

                if (isset($this->rules[$keyMethods])) {
                    if (isset($this->rules[$keyMethods][$routeUrl])) {
                        foreach ($this->rules[$keyMethods][$routeUrl] as $keyUriRule => $classRule) {
                            $objectClassRule = new $classRule();
                            $requiredParam = $objectClassRule->disabled === false ? 'REQUIRED' : 'OPTIONAL';

                            $rows[] = [
                                $this->successOutput('PARAM:'),
                                (
                                    empty($objectClassRule->field)
                                        ? $this->successOutput('(NAMELESS)')
                                        : $this->successOutput($objectClassRule->field . " ({$requiredParam})")
                                ),
                                $this->transformNamespace($objectClassRule::class),
                                $this->warningOutput('passes')
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
            ->setHeaders(['METHOD', 'ROUTE', 'CLASS', 'FUNCTION'])
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
