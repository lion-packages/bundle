<?php

namespace App\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\{ Table, TableSeparator };

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
        $url = env->SERVER_URL . "route-list";
        $routes = (array) json_decode(file_get_contents($url));
        array_pop($routes);

        $table = new Table($output);
        // $table->setStyle('box');

        $rows = [];
        // $i = 0;
        // $total_cont = count($routes);
        foreach ($routes as $key => $route) {
            $route = (array) $route;

            foreach ($route as $key2 => $info_route) {
                $info_route = (array) $info_route;
                $controller = (array) $info_route[0];

                $rows[] = [
                    // ($i + 1),
                    "<comment>{$key2}</comment>",
                    ($key === '' ? '/' : $key),
                    isset($controller[0]) ? $controller[0] : '',
                    isset($controller[1]) ? $controller[1] : 'callback'
                ];

                // if ($i < ($total_cont - 1)) $rows[] = new TableSeparator();
            }

            // $i++;
        }

        $table->setHeaders([/* '#', */ 'TYPE', 'URL', 'CONTROLLER', 'METHOD'])->setRows($rows);
        $table->render();

        return Command::SUCCESS;
    }

}