<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion;

use Lion\Command\Command;
use Lion\Command\Kernel;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Initialize the local server with PHP
 *
 * @property Kernel $kernel [Kernel class object]
 *
 * @package Lion\Bundle\Commands\Lion
 */
class ServerCommand extends Command
{
    /**
     * [Kernel class object]
     *
     * @var Kernel $kernel
     */
    private Kernel $kernel;

    /**
     * @required
     * */
    public function setKernel(Kernel $kernel): ServerCommand
    {
        $this->kernel = $kernel;

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
            ->setName('serve')
            ->setDescription('Created command to start server locally')
            ->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Do you want to set your own port?', 8000)
            ->addOption('host', 's', InputOption::VALUE_OPTIONAL, 'Do you want to set your own host?', '127.0.0.1');
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
		$port = $input->getOption('port');

        $host = $input->getOption('host');

        $url = "{$host}:{$port}";

        $link = "<href=http://{$url}>[http://{$url}]</>";

        $output->write("\033[2J\033[;H");

        $output->write($this->successOutput("\nLion-Framework "));

        $output->writeln("ready in " . number_format((microtime(true) - LION_START), 3) . " ms\n");

        $output->writeln($this->warningOutput("\t>>  LOCAL:</comment> Server running on {$link}"));

        $output->writeln($this->warningOutput("\t>>  HOST:</comment> use --host to expose"));

        $output->writeln($this->warningOutput("\t>>  PORT:</comment> use --port to expose"));

        $output->writeln($this->warningOutput("\nPress Ctrl+C to stop the server\n"));

        $this->kernel->execute("php -S {$host}:{$port} -t public", false);

        return Command::SUCCESS;
	}
}
