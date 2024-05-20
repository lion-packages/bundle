<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion;

use Lion\Command\Command;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Run the tests defined with PHPunit
 *
 * @package Lion\Bundle\Commands\Lion
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
     * @return int 0 if everything went fine, or an exit code
     *
     * @throws LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $application = new Application();

        $application->setAutoExit(false);

        $phpBinaryPath = (new PhpExecutableFinder())->find();

        $commandString = "{$phpBinaryPath} ./vendor/bin/phpunit";

        if ($input->getOption('suite')) {
            $commandString .= ' --testsuite ' . $input->getOption('suite');
        }

        if ($input->getOption('class')) {
            $commandString .= ' ./tests/' . $input->getOption('class') . '.php';
        }

        if ($input->getOption('method')) {
            $commandString .= ' --filter ' . $input->getOption('method');
        }

        if ('none' != $input->getOption('report')) {
            $commandString .= ' --coverage-clover tests/build/logs/clover.xml --coverage-html tests/build/coverage';
        }

        $process = new Process(explode(' ', $commandString));

        $process->setTimeout(null);

        if (Process::isTtySupported()) {
            $process->setTty(true);
        }

        $process->run(function ($type, $buffer): void {
            echo $buffer;
        });

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return Command::SUCCESS;
    }
}
