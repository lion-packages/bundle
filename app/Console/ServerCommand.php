<?php

namespace App\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument, InputOption };
use Symfony\Component\Console\Output\OutputInterface;

class ServerCommand extends Command {

	protected static $defaultName = "serve";

	protected function initialize(InputInterface $input, OutputInterface $output) {

	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this->setDescription(
			"created command to start server locally"
		)->addOption(
			'port', null, InputOption::VALUE_REQUIRED, 'How many times should the message be printed?'
		);
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$port = $input->getOption('port');
		if (!$port) {
			$port = 4040;
		}

		shell_exec("php -S localhost:{$port}");
		return Command::SUCCESS;
	}

}