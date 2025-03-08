<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\Route;

use DI\Attribute\Inject;
use GuzzleHttp\Exception\GuzzleException;
use Lion\Bundle\Helpers\Http\Fetch;
use Lion\Bundle\Helpers\Http\Routes;
use Lion\Command\Command;
use Lion\Helpers\Arr;
use Lion\Helpers\Str;
use Lion\Route\Helpers\Rules;
use Lion\Route\Route;
use LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;

/**
 * Shows a table with the defined web routes and their properties
 *
 * @package Lion\Bundle\Commands\Lion\Route
 */
class RouteListCommand extends Command
{
    /**
     * [Modify and build arrays with different indexes or values]
     *
     * @var Arr $arr
     */
    private Arr $arr;

    /**
     * [Modify and construct strings with different formats]
     *
     * @var Str $str
     */
    private Str $str;

    /**
     * [List of defined web routes]
     *
     * @var array{
     *     array<string, array{
     *          filters: array<int, string>,
     *          handler: array{
     *              controller: bool|array{
     *                  name: string,
     *                  function: string
     *              },
     *              callback: bool
     *          }
     *     }>
     * } $routes
     */
    private array $routes;

    /**
     * [List of defined Middleware]
     *
     * @var array<string, class-string> $configMiddleware
     */
    private array $configMiddleware = [];

    #[Inject]
    public function setArr(Arr $arr): RouteListCommand
    {
        $this->arr = $arr;

        return $this;
    }

    #[Inject]
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
     * @return int
     *
     * @throws LogicException [When this abstract method is not implemented]
     * @throws GuzzleException
     *
     * @codeCoverageIgnore
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

                if ($method['handler']['callback']) {
                    $rows[] = [
                        $this->warningOutput($keyMethods),
                        $routeUrl,
                        $this->errorOutput('false'),
                        $this->errorOutput('callback')
                    ];
                }

                if ($method['handler']['controller']) {
                    $rows[] = [
                        $this->warningOutput($keyMethods),
                        $routeUrl,
                        /** @phpstan-ignore-next-line */
                        $method['handler']['controller']['name'],
                        /** @phpstan-ignore-next-line */
                        $this->warningOutput($method['handler']['controller']['function'])
                    ];
                }

                if ($this->arr->of($method['filters'])->length() > 0) {
                    foreach ($method['filters'] as $filter) {
                        if (isset($this->configMiddleware[$filter])) {
                            $rows[] = [
                                $this->infoOutput('MIDDLEWARE'),
                                $this->infoOutput($filter),
                                $this->configMiddleware[$filter],
                                $this->warningOutput('process')
                            ];
                        }
                    }
                }

                /** @phpstan-ignore-next-line */
                if (isset($this->rules[$keyMethods])) {
                    /** @phpstan-ignore-next-line */
                    if (isset($this->rules[$keyMethods][$routeUrl])) {
                        /** @phpstan-ignore-next-line */
                        foreach ($this->rules[$keyMethods][$routeUrl] as $keyUriRule => $classRule) {
                            /** @var Rules $objectClassRule */
                            $objectClassRule = new $classRule();

                            if (isset($objectClassRule->disabled, $objectClassRule->field)) {
                                $requiredParam = $objectClassRule->disabled === false ? 'REQUIRED' : 'OPTIONAL';

                                /** @var string $field */
                                $field = $objectClassRule->field;

                                $rows[] = [
                                    $this->successOutput('PARAM:'),
                                    (
                                        empty($objectClassRule->field)
                                            ? $this->successOutput('(NAMELESS)')
                                            : $this->successOutput("{$field} ({$requiredParam})")
                                    ),
                                    $objectClassRule::class,
                                    $this->warningOutput('passes')
                                ];
                            }
                        }
                    }
                }

                $rows[] = new TableSeparator();

                $cont++;
            }
        }

        new Table($output)
            ->setHeaderTitle($this->successOutput('ROUTES'))
            ->setFooterTitle($this->successOutput(" Showing [{$cont}] routes "))
            ->setHeaders(['HTTP METHOD', 'ROUTE', 'CLASS', 'FUNCTION'])
            ->setRows($rows)
            ->render();

        return parent::SUCCESS;
    }

    /**
     * Gets the parameters (Web Routes/Rules/Middleware) of the defined web
     * routes
     *
     * @return void
     *
     * @throws GuzzleException
     */
    private function fetchRoutes(): void
    {
        /** @var string $serverUrl */
        $serverUrl = env('SERVER_URL');

        $fetchRoutes = fetch(
            new Fetch(Route::GET, "{$serverUrl}/route-list", [
                'headers' => [
                    'Lion-Auth' => $_ENV['SERVER_HASH'],
                ],
            ])
        )
            ->getBody()
            ->getContents();

        /**
         * @var array{
         *     array<string, array{
         *          filters: array<int, string>,
         *          handler: array{
         *              controller: bool|array{
         *                  name: string,
         *                  function: string
         *              },
         *              callback: bool
         *          }
         *     }>
         * } $routes
         */
        $routes = json_decode($fetchRoutes, true);

        array_pop($routes);

        /** @phpstan-ignore-next-line */
        $this->routes = $routes;

        $this->configMiddleware = Routes::getMiddleware();
    }
}
