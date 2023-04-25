<?php

namespace App\Console\Framework\Route;

use LionHelpers\Arr;
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
        $this->setDescription("Command to view a list of available web routes");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->getFormatter()->setStyle('fire', new OutputFormatterStyle('blue'));
        $routes = (array) json_decode(file_get_contents(env->SERVER_URL . "/route-list"));
        array_pop($routes);
        $size = Arr::of($routes)->length();
        $cont = 0;
        $rows = [];

        foreach ($routes as $key => $route) {
            $route = (array) $route;

            foreach ($route as $key2 => $info_route) {
                $info_route = (array) $info_route;
                $controller = (array) $info_route[0];
                $middleware = [];
                $middleware_values = array_values((array) $info_route[1]);

                if (Arr::of($middleware_values)->length() > 0) {
                    foreach ($middleware_values as $key_values => $midd) {
                        if (gettype($midd) === "string") {
                            $middleware[] = $midd;
                        } else {
                            foreach ($midd as $key_midd => $value_midd) {
                                $middleware[] = $value_midd;
                            }
                        }
                    }
                }

                $rows[] = [
                    "<comment>{$key2}</comment>",
                    ($key === '' ? '/' : $key),
                    isset($controller[0]) ? $controller[0] : '',
                    isset($controller[1]) ? $controller[1] : 'callback'
                ];

                if (Arr::of($middleware)->length() > 0) {
                    // if ($cont < ($size - 1)) {
                        // $rows[] = new TableSeparator();
                    // }

                    $rows[] = [
                        new TableCell(
                            "<fire>MIDDLEWARE:</fire>",
                            ['colspan' => 1]
                        ),
                        new TableCell(
                            "<fire>" . Arr::of($middleware)->join(" | ") . "</fire>",
                            ['colspan' => 3]
                        )
                    ];
                }

                if ($cont < ($size - 1)) {
                    $rows[] = new TableSeparator();
                }
            }

            $cont++;
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
            ->setHeaders(['METHOD', 'ROUTE', 'CONTROLLER', 'FUNCTION'])
            ->setRows($rows)
            ->render();

        return Command::SUCCESS;
    }

}