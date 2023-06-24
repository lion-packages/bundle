<?php

namespace App\Console\Framework\Resources;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ServerResourcesCommand extends Command {

	protected static $defaultName = "resource:serve";

	protected function initialize(InputInterface $input, OutputInterface $output) {

	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
        $this
            ->setDescription("Command required to run resources")
            ->addArgument('resource', InputArgument::REQUIRED, 'Enum name')
            ->addOption('port', "p", InputOption::VALUE_OPTIONAL, 'Do you want to set your own port?', 5173)
            ->addOption('host', "s", InputOption::VALUE_OPTIONAL, 'Do you want to set your own port?', "127.0.0.1");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $resource = $input->getArgument("resource");
        $port = $input->getOption("port");
        $host = $input->getOption("host");
        kernel->execute("php -S {$host}:{$port} -t resources/{$resource}/", false);

        return Command::SUCCESS;
    }

}
