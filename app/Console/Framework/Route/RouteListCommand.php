<?php

namespace App\Console\Framework\Route;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\{ Table, TableCell, TableSeparator };

class RouteListCommand extends Command {

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
        $output->getFormatter()->setStyle('fire', new OutputFormatterStyle('blue'));
        $routes = fetch("GET", env->SERVER_URL . "/route-list");
        array_pop($routes);
        $size = arr->of($routes)->length();
        $cont = 0;
        $rows = [];

        foreach ($routes as $route => $methods) {
            foreach ($methods as $keyMethods => $method) {
                if ($method['handler']['request'] != false) {
                    $rows[] = [
                        "<comment>{$keyMethods}</comment>",
                        str->of("/{$route}")->replace("//", "/")->get(),
                        '<fg=#E37820>false</>',
                        '<fg=#E37820>false</>',
                        "<href={$method['handler']['request']['url']}>[{$method['handler']['request']['url']}]</>"
                    ];
                }

                if ($method['handler']['callback'] != false) {
                    $rows[] = [
                        "<comment>{$keyMethods}</comment>",
                        str->of("/{$route}")->replace("//", "/")->get(),
                        '<fg=#E37820>false</>',
                        '<fg=#E37820>callback</>',
                        "<fg=#E37820>false</>",
                    ];
                }

                if ($method['handler']['controller'] != false) {
                    $rows[] = [
                        "<comment>{$keyMethods}</comment>",
                        str->of("/{$route}")->replace("//", "/")->get(),
                        $method['handler']['controller']['name'],
                        $method['handler']['controller']['function'],
                        "<fg=#E37820>false</>"
                    ];
                }

                if (arr->of($method['filters'])->length() > 0) {
                    $rows[] = [
                        new TableCell(
                            "<fire>MIDDLEWARE:</fire>",
                            ['colspan' => 1]
                        ),
                        new TableCell(
                            "<fire>" . arr->of($method['filters'])->join(" | ") . "</fire>",
                            ['colspan' => 3]
                        )
                    ];
                }

                if ($cont < ($size - 1)) {
                    $rows[] = new TableSeparator();
                }

                $cont++;
            }
        }

        (new Table($output))
            ->setHeaderTitle('<info> ROUTES </info>')
            ->setFooterTitle(
                $size > 1
                ? "<info> Showing [" . $size . "] routes </info>"
                : ($size === 1
                    ? "<info> showing a single route </info>"
                    : "<info> No routes available </info>"
                )
            )
            ->setHeaders(['METHOD', 'ROUTE', 'CONTROLLER', 'FUNCTION', 'REQUEST'])
            ->setRows($rows)
            ->render();

        return Command::SUCCESS;
    }

}
