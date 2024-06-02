<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Route;

use Lion\Bundle\Helpers\Http\Routes;
use Lion\Command\Command;
use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use Lion\Route\Middleware;
use Lion\Route\Route;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;

/**
 * Shows a table with the defined web routes and their properties
 *
 * @property Arr $arr [Arr class object]
 * @property Str $str [Str class object]
 *
 * @package Lion\Bundle\Commands\Lion\Route
 */
class RouteListCommand extends Command
{
    /**
     * [Arr class object]
     *
     * @var Arr $arr
     */
    private Arr $arr;

    /**
     * [Str class object]
     *
     * @var Str $str
     */
    private Str $str;

    /**
     * [List of defined web routes]
     *
     * @var array $routes
     */
    private array $routes = [];

    /**
     * [List of defined rules]
     *
     * @var array $rules
     */
    private array $rules = [];

    /**
     * [List of defined Middleware]
     *
     * @var array<Middleware> $configMiddleware
     */
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

    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('route:list')
            ->setDescription('Command to view a list of available web routes');
    }

    /**
     * Executes the current command
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method
     *
     * @param InputInterface $input [InputInterface is the interface implemented
     * by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return int 0 if everything went fine, or an exit code
     *
     * @throws LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->fetchRoutes();

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
                        $method['handler']['controller']['name'],
                        $this->warningOutput($method['handler']['controller']['function'])
                    ];
                }

                if ($this->arr->of($method['filters'])->length() > 0) {
                    foreach ($method['filters'] as $filter) {
                        foreach ($this->configMiddleware as $middleware) {
                            if ($filter === $middleware->getMiddlewareName()) {
                                $rows[] = [
                                    $this->infoOutput('MIDDLEWARE:'),
                                    $this->infoOutput($filter),
                                    $middleware->getClass(),
                                    $this->warningOutput($middleware->getMethodClass())
                                ];
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
                                $objectClassRule::class,
                                $this->warningOutput('passes')
                            ];
                        }
                    }
                }

                $rows[] = new TableSeparator();

                $cont++;
            }
        }

        (new Table($output))
            ->setHeaderTitle($this->successOutput('ROUTES'))
            ->setFooterTitle(
                $size > 1
                    ? $this->successOutput(" showing [{$cont}] routes ")
                    : (
                        $size === 1
                        ? $this->successOutput(' showing a single route ')
                        : $this->successOutput(' no routes available ')
                    )
            )
            ->setHeaders(['HTTP METHOD', 'ROUTE', 'CLASS', 'FUNCTION'])
            ->setRows($rows)
            ->render();

        return Command::SUCCESS;
    }

    /**
     * Gets the parameters (Web Routes/Rules/Middleware) of the defined web
     * routes
     *
     * @return void
     */
    private function fetchRoutes(): void
    {
        $this->routes = json_decode(
            fetch(Route::GET, ($_ENV['SERVER_URL'] . '/route-list'), [
                'headers' => [
                    'Lion-Auth' => $_ENV['SERVER_HASH']
                ]
            ])->getBody()->getContents(),
            true
        );

        array_pop($this->routes);

        $this->configMiddleware = Routes::getMiddleware();
    }
}
