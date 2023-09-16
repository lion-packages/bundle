<?php

namespace App\Console\Framework;

use App\Traits\Framework\ConsoleOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunTestCommand extends Command
{
    use ConsoleOutput;

	protected static $defaultName = "test";

	protected function initialize(InputInterface $input, OutputInterface $output)
	{
        $output->writeln($this->warningOutput("\t>>  Running unit tests..."));
	}

	protected function interact(InputInterface $input, OutputInterface $output)
	{

	}

	protected function configure()
	{
		$this
            ->setDescription("Command to create run unit tests")
            ->addOption('class', 'c', InputOption::VALUE_OPTIONAL, 'Do you want to run a specific class?', false)
            ->addOption('method', 'm', InputOption::VALUE_OPTIONAL, 'Do you want to filter a specific method?', false);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$result = '';
		$class = $input->getOption('class');
		$method = $input->getOption('method');

		if (!$class && !$method) {
			$result = exec('./vendor/bin/phpunit');
		}

		if ($class && !$method) {
			$result = exec("./vendor/bin/phpunit tests/{$class}.php");
		}

		if ($class && $method) {
			$result = exec("./vendor/bin/phpunit tests/{$class}.php --filter {$method}");
		}

        $output->writeln($this->warningOutput("\t>>  {$result}"));
        return Command::SUCCESS;
	}
}
