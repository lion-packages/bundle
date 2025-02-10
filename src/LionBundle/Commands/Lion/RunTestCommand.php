<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion;

use Lion\Command\Command;
use LogicException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Run the tests defined with PHPUnit
 *
 * @package Lion\Bundle\Commands\Lion
 *
 * @codeCoverageIgnore
 */
class RunTestCommand extends Command
{
    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('test')
            ->setDescription('Runs the PHPUnit tests')
            ->setHelp('This command allows you to run PHPUnit tests via the Symfony Console command')
            ->addOption('class', 'c', InputOption::VALUE_OPTIONAL, 'The class to test')
            ->addOption('method', 'm', InputOption::VALUE_OPTIONAL, 'The method to test')
            ->addOption('suite', 's', InputOption::VALUE_OPTIONAL, 'The test suite to run')
            ->addOption('report', 'r', InputOption::VALUE_OPTIONAL, 'The test suite with coverage report', 'none');
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
     * @return int
     *
     * @throws LogicException [When this abstract method is not implemented]
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $application = new Application();

        $application->setAutoExit(false);

        $phpBinaryPath = new PhpExecutableFinder()->find();

        $commandString = "{$phpBinaryPath} ./vendor/bin/phpunit";

        /** @var string|null $suite */
        $suite = $input->getOption('suite');

        /** @var string|null $class */
        $class = $input->getOption('class');

        /** @var string|null $method */
        $method = $input->getOption('method');

        /** @var string|null $report */
        $report = $input->getOption('report');

        if (!empty($suite)) {
            $commandString .= " --testsuite {$suite}";
        }

        if (!empty($class)) {
            $commandString .= " ./tests/{$class}.php";
        }

        if (!empty($method)) {
            $commandString .= " --filter {$method}";
        }

        if (empty($report)) {
            $commandString .= ' --coverage-clover tests/build/logs/clover.xml --coverage-html tests/build/coverage';
        }

        $process = new Process(explode(' ', $commandString));

        $process->setTimeout(null);

        if (Process::isTtySupported()) {
            $process->setTty(true);
        }

        $process->run(function ($type, string $buffer): void {
            echo $buffer;
        });

        return parent::SUCCESS;
    }
}
