<?php

namespace App\Console\Framework\Route;

use App\Traits\Framework\ConsoleOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\{ Table, TableCell, TableSeparator };

class RouteListCommand extends Command {

    use ConsoleOutput;

	protected static $defaultName = "route:list";

	protected function initialize(InputInterface $input, OutputInterface $output) {

    }

    protected function interact(InputInterface $input, OutputInterface $output) {

    }

    protected function configure() {
        $this
            ->setDescription("Command to view a list of available web routes");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->getFormatter()->setStyle('lion', new OutputFormatterStyle('blue'));
        $routes = fetch("GET", env->SERVER_URL . "/route-list");
        array_pop($routes);
        $rules = require_once("./routes/rules.php");
        $size = arr->of($routes)->length();
        $cont = 0;
        $rows = [];

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
                    $controller = "";
                    $split = explode("\\", $method['handler']['controller']['name']);

                    foreach ($split as $key => $value) {
                        if ($key < (count($split) - 1)) {
                            $controller .= $this->purpleOutput($value) . "\\";
                        } else {
                            $controller .= $value;
                        }
                    }

                    $rows[] = [
                        $this->warningOutput($keyMethods),
                        $route_url,
                        $controller,
                        $this->warningOutput($method['handler']['controller']['function']),
                        $this->errorOutput("false"),
                    ];
                }

                if (arr->of($method['filters'])->length() > 0) {
                    foreach ($method['filters'] as $key => $filter) {
                        $rows[] = [
                            new TableCell(
                                $this->infoOutput("MIDDLEWARE:"),
                                ['colspan' => 1]
                            ),
                            new TableCell(
                                $this->infoOutput($filter),
                                ['colspan' => 1]
                            )
                        ];
                    }
                }

                if (isset($rules[$keyMethods])) {
                    if (isset($rules[$keyMethods][$route_url])) {
                        foreach ($rules[$keyMethods][$route_url] as $key_uri_rule => $class_rule) {
                            $required_param = $class_rule::$disabled === false ? "REQUIRED" : "OPTIONAL";

                            $rows[] = [
                                new TableCell(
                                    $this->successOutput("PARAM:"),
                                    ['colspan' => 1]
                                ),
                                new TableCell(
                                    $this->successOutput($class_rule::$field . " ({$required_param})"),
                                    ['colspan' => 1]
                                )
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
                ? $this->successOutput(" showing [" . $size . "] routes ")
                : ($size === 1
                    ? $this->successOutput(" showing a single route ")
                    : $this->successOutput(" no routes available ")
                )
            )
            ->setHeaders(['METHOD', 'ROUTE', 'CONTROLLER', 'FUNCTION', 'REQUEST'])
            ->setRows($rows)
            ->render();

        return Command::SUCCESS;
    }

}
