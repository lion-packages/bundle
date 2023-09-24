<?php

namespace App\Console\Framework;

use App\Traits\Framework\ConsoleOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command
{
    use ConsoleOutput;

	protected static $defaultName = "init-project";

	protected function initialize(InputInterface $input, OutputInterface $output)
	{
        $output->write("\033[2J\033[;H");
	}

	protected function interact(InputInterface $input, OutputInterface $output)
	{

	}

	protected function configure()
	{
		$this
            ->setDescription("Command to show basic information to access the project");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
        $output->writeln($this->warningOutput("\nScaffolding project in " . getcwd() . "..."));
        $output->writeln($this->successOutput("\nDone. Now run:\n"));
        $output->writeln("  cd " . basename(realpath('.')));
        $output->writeln("  php lion serve\n");
        return Command::SUCCESS;
    }
}
