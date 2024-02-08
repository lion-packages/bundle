<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion;

use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Helpers\Arr;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunTestCommand extends Command
{
    private Kernel $kernel;
    private Arr $arr;

    /**
     * @required
     * */
    public function setKernel(Kernel $kernel): RunTestCommand
    {
        $this->kernel = $kernel;

        return $this;
    }

    /**
     * @required
     * */
    public function setArr(Arr $arr): RunTestCommand
    {
        $this->arr = $arr;

        return $this;
    }

	protected function configure(): void
	{
		$this
            ->setName('test')
            ->setDescription('Command to create run unit tests')
            ->addOption('class', 'c', InputOption::VALUE_OPTIONAL, 'Do you want to run a specific class?', false)
            ->addOption('method', 'm', InputOption::VALUE_OPTIONAL, 'Do you want to filter a specific method?', false)
            ->addOption('suite', 's', InputOption::VALUE_OPTIONAL, 'Do you want to test a specific directory?', 'All-Test');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$result = '';
		$class = $input->getOption('class');
		$method = $input->getOption('method');
		$suite = $input->getOption('suite');

        $output->writeln($this->successOutput("\t>>  Running unit tests...\n\t>>  "));

		if (!$class && !$method) {
			$result = $this->kernel->execute("./vendor/bin/phpunit --testsuite {$suite}", false);
		}

		if ($class && !$method) {
			$result = $this->kernel->execute("./vendor/bin/phpunit tests/{$class}.php --testsuite {$suite}", false);
		}

		if ($class && $method) {
			$result = $this->kernel->execute(
				"./vendor/bin/phpunit tests/{$class}.php --filter {$method} --testsuite {$suite}",
				false
			);
		}

		$output->writeln($this->warningOutput("\t>>  " . $this->arr->of($result)->join("\n\t>>  ")));

        return Command::SUCCESS;
	}
}
