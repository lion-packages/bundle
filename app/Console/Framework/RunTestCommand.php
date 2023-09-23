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
		$output->write("\033[2J\033[;H");
        $output->writeln($this->successOutput("\t>>  Running unit tests...\n\t>>  "));
	}

	protected function interact(InputInterface $input, OutputInterface $output)
	{

	}

	protected function configure()
	{
		$this
            ->setDescription("Command to create run unit tests")
            ->addOption('class', 'c', InputOption::VALUE_OPTIONAL, 'Do you want to run a specific class?', false)
            ->addOption('method', 'm', InputOption::VALUE_OPTIONAL, 'Do you want to filter a specific method?', false)
            ->addOption('suite', 's', InputOption::VALUE_OPTIONAL, 'Do you want to test a specific directory?', 'All-Testing');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$result = '';
		$class = $input->getOption('class');
		$method = $input->getOption('method');
		$suite = $input->getOption('suite');

		if (!$class && !$method) {
			$result = kernel->execute("./vendor/bin/phpunit --testsuite {$suite}", false);
		}

		if ($class && !$method) {
			$result = kernel->execute("./vendor/bin/phpunit tests/{$class}.php --testsuite {$suite}", false);
		}

		if ($class && $method) {
			$result = kernel->execute(
				"./vendor/bin/phpunit tests/{$class}.php --filter {$method} --testsuite {$suite}",
				false
			);
		}

		$output->writeln($this->warningOutput("\t>>  " . arr->of($result)->join("\n\t>>  ")));
        return Command::SUCCESS;
	}
}
