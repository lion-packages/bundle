<?php

namespace App\Console\Framework;

use App\Console\Kernel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputOption };
use Symfony\Component\Console\Output\OutputInterface;

class ServerCommand extends Command {

	protected static $defaultName = "serve";

	protected function initialize(InputInterface $input, OutputInterface $output) {
        $output->write("\033[2J\033[;H");
        $output->write("\n<info>Lion-Framework</info> ");
        $output->writeln("ready in " . number_format((microtime(true) - LION_START), 3) . " ms\n");
	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this->setDescription(
            "Created command to start server locally"
        )->addOption(
            'port', null, InputOption::VALUE_REQUIRED, 'Do you want to set your own port?'
        )->addOption(
            'host', null, InputOption::VALUE_REQUIRED, 'Do you want to set your own host?'
        );
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$port = $input->getOption('port');
        $host = $input->getOption('host');

        if ($port === null) {
            $port = 8000;
        }

        if ($host === null) {
            $host = "127.0.0.1";
        }

        $url = "{$host}:{$port}";
        $output->writeln("\t<question> INFO </question> Server running on <href=http://{$url}>[http://{$url}]</>\n");
        $output->writeln("<comment>Press Ctrl+C to stop the server</comment>\n");
        Kernel::getInstance()->execute("php -S {$host}:{$port} -t public");
        return Command::SUCCESS;
	}

}
