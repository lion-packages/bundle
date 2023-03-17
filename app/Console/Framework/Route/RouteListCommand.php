<?php

namespace App\Console\Framework\Route;

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
        $routes = (array) json_decode(file_get_contents(env->SERVER_URL . "/route-list"));
        array_pop($routes);

        $rows = [];
        foreach ($routes as $key => $route) {
            $route = (array) $route;

            foreach ($route as $key2 => $info_route) {
                $info_route = (array) $info_route;
                $controller = (array) $info_route[0];

                if (count($controller) === 0) {
                    $rows[] = [
                        "<comment>{$key2}</comment>",
                        ($key === '' ? '/' : $key),
                        '',
                        '',
                        'true'
                    ];
                } else {
                    $rows[] = [
                        "<comment>{$key2}</comment>",
                        ($key === '' ? '/' : $key),
                        isset($controller[0]) ? $controller[0] : '',
                        isset($controller[1]) ? $controller[1] : 'callback',
                        'false'
                    ];
                }
            }
        }

        (new Table($output))
            ->setHeaders(['TYPE', 'URL', 'CONTROLLER', 'METHOD', 'REQUEST'])
            ->setRows($rows)
            ->render();

        return Command::SUCCESS;
    }

}