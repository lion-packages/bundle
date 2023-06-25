<?php

namespace App\Console\Framework;

use App\Traits\Framework\ConsoleOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunTestCommand extends Command {

    use ConsoleOutput;

	protected static $defaultName = "test";

	protected function initialize(InputInterface $input, OutputInterface $output) {
        $output->writeln($this->warningOutput("\t>>  Running unit tests..."));
	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this
            ->setDescription("Command to create run unit tests");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		$result = exec('./vendor/bin/phpunit');
        $output->writeln($this->warningOutput("\t>>  {$result}"));
        return Command::SUCCESS;
	}

}
