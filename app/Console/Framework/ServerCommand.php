<?php

namespace App\Console\Framework;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputOption };
use Symfony\Component\Console\Output\OutputInterface;

class ServerCommand extends Command {

	protected static $defaultName = "serve";

	protected function initialize(InputInterface $input, OutputInterface $output) {
        $output->write("\n<info>Lion-Framework</info> ");
        $output->writeln("ready in " . number_format((microtime(true) - LION_START), 3) . " ms\n");
	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this->setDescription(
            "created command to start server locally"
        )->addOption(
            'port', null, InputOption::VALUE_REQUIRED, 'Do you want to set your own port?'
        );
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$port = $input->getOption('port');

        if ($port === null) {
            $port = 8000;
        }

        $host = "127.0.0.1:{$port}";
        $output->writeln("\t<question> INFO </question> Server running on <href=http://{$host}>[http://{$host}]</>\n");
        $output->writeln("<comment>Press Ctrl+C to stop the server</comment>\n");
        exec("php -S $host -t public");

        return Command::SUCCESS;
	}

}