<?php

namespace App\Console\Framework;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command {

	protected static $defaultName = "init-project";

	protected function initialize(InputInterface $input, OutputInterface $output) {
        $output->write("\033[2J\033[;H");
        $output->writeln("\n<comment>Scaffolding project in " . getcwd() . "...</comment>");
	}

	protected function interact(InputInterface $input, OutputInterface $output) {

	}

	protected function configure() {
		$this->setDescription("Command to show basic information to access the project");
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln("\n<info>Done. Now run:</info>\n");
        $output->writeln("  cd " . basename(realpath('.')));
        $output->writeln("  php lion serve\n");
        return Command::SUCCESS;
    }

}
