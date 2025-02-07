<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion;

use Lion\Bundle\Helpers\Commands\ProcessCommand;
use Lion\Command\Command;
use LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Initialize the local server with PHP
 *
 * @package Lion\Bundle\Commands\Lion
 *
 * @codeCoverageIgnore
 */
class ServerCommand extends Command
{
    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('serve')
            ->setDescription('Created command to start server locally')
            ->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Do you want to set your own port?', 8000)
            ->addOption('host', 's', InputOption::VALUE_OPTIONAL, 'Do you want to set your own host?', 'localhost');
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
        /** @phpstan-ignore-next-line */
        $time = number_format((microtime(true) - LION_START), 3);

        /** @var string $port */
        $port = $input->getOption('port');

        /** @var string $host */
        $host = $input->getOption('host');

        $url = "{$host}:{$port}";

        $link = "<href=http://{$url}>[http://{$url}]</>";

        $output->write("\033[2J\033[;H");

        $output->write($this->successOutput("\nLion-Framework "));

        $output->writeln("ready in {$time} ms\n");

        $output->writeln("\t>>  LOCAL: Server running on " . $this->warningOutput("{$link}"));

        $output->writeln("\t>>  HOST: " . $this->warningOutput("use --host to expose"));

        $output->writeln("\t>>  PORT: " . $this->warningOutput("use --port to expose"));

        $output->writeln($this->warningOutput("\nPress Ctrl+C to stop the server\n"));

        ProcessCommand::run("php -S {$host}:{$port} -t public");

        return Command::SUCCESS;
    }
}
