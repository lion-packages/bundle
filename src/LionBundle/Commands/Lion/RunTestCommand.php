<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion;

use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\DependencyInjection\Container;
use Lion\Helpers\Arr;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Run the tests defined with PHPunit
 *
 * @property Container $container [Container class object]
 * @property Kernel $kernel [Kernel class object]
 * @property Arr $arr [Arr class object]
 *
 * @package Lion\Bundle\Commands\Lion
 */
class RunTestCommand extends Command
{
    /**
     * [Container class object]
     *
     * @var Container $container
     */
    private Container $container;

    /**
     * [Kernel class object]
     *
     * @var Kernel $kernel
     */
    private Kernel $kernel;

    /**
     * [Arr class object]
     *
     * @var Arr $arr
     */
    private Arr $arr;

    /**
     * @required
     * */
    public function setContainer(Container $container): RunTestCommand
    {
        $this->container = $container;

        return $this;
    }

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

    /**
     * Configures the current command
     *
     * @return void
     */
	protected function configure(): void
	{
		$this
            ->setName('test')
            ->setDescription('Command to create run unit tests')
            ->addOption('class', 'c', InputOption::VALUE_OPTIONAL, 'Do you want to run a specific class?', false)
            ->addOption('method', 'm', InputOption::VALUE_OPTIONAL, 'Do you want to filter a specific method?', false)
            ->addOption(
                'suite',
                's',
                InputOption::VALUE_OPTIONAL,
                'Do you want to test a specific directory?',
                'All-Test'
            );
	}

    /**
     * Executes the current command
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method
     *
     * @param InputInterface $input [InputInterface is the interface implemented
     * by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return int 0 if everything went fine, or an exit code
     *
     * @throws LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$result = '';

		$class = $input->getOption('class');

		$method = $input->getOption('method');

		$suite = $input->getOption('suite');

        $output->writeln($this->successOutput("\t>>  Running unit tests...\n\t>>  "));

        $unitPath = $this->container->normalizePath('./vendor/bin/phpunit');

		if (!$class && !$method) {
			$result = $this->kernel->execute("{$unitPath} --testsuite {$suite}", false);
		}

		if ($class && !$method) {
			$result = $this->kernel->execute("{$unitPath} tests/{$class}.php --testsuite {$suite}", false);
		}

		if ($class && $method) {
			$result = $this->kernel->execute(
				"{$unitPath} tests/{$class}.php --filter {$method} --testsuite {$suite}",
				false
			);
		}

		$output->writeln($this->warningOutput("\t>>  " . $this->arr->of($result)->join("\n\t>>  ")));

        return Command::SUCCESS;
	}
}
