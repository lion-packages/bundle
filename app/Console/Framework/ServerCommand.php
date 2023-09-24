<?php

namespace App\Console\Framework;

use App\Traits\Framework\ConsoleOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ServerCommand extends Command
{
    use ConsoleOutput;

	protected static $defaultName = "serve";

	protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $output->write("\033[2J\033[;H");
        $output->write($this->successOutput("\nLion-Framework "));
        $output->writeln("ready in " . number_format((microtime(true) - LION_START), 3) . " ms\n");
	}

	protected function interact(InputInterface $input, OutputInterface $output)
    {

	}

	protected function configure()
    {
		$this
            ->setDescription("Created command to start server locally")
            ->addOption('port', "p", InputOption::VALUE_OPTIONAL, 'Do you want to set your own port?', 8000)
            ->addOption('host', "s", InputOption::VALUE_OPTIONAL, 'Do you want to set your own host?', "127.0.0.1");
	}

	protected function execute(InputInterface $input, OutputInterface $output)
    {
		$port = $input->getOption('port');
        $host = $input->getOption('host');
        $url = "{$host}:{$port}";
        $link = "<href=http://{$url}>[http://{$url}]</>";

        $output->writeln($this->warningOutput("\t>>  LOCAL:</comment> Server running on {$link}"));
        $output->writeln($this->warningOutput("\t>>  HOST:</comment> use --host to expose"));
        $output->writeln($this->warningOutput("\t>>  PORT:</comment> use --port to expose"));
        $output->writeln($this->warningOutput("\nPress Ctrl+C to stop the server\n"));
        kernel->execute("php -S {$host}:{$port} -t public", false);

        return Command::SUCCESS;
	}
}
